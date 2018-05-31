<?php

class LWM_ConfigSimplePrice_Model_Observer
{
    public function getFinalPrice(Varien_Event_Observer $observer) {
        $event   = $observer->getEvent();
        $product = $event->getProduct();
        $qty     = $event->getQty();
        $selectedAttributes = array();
        if ($product->getCustomOption('attributes')) {
            Mage::log('enter-----', null, 'confPricing.log');
            $selectedAttributes = unserialize($product->getCustomOption('attributes')->getValue());
        }
        if (sizeof($selectedAttributes)) {
            return $this->getSimpleProductPrice($qty, $product);
        }
    }

    public function getSimpleProductPrice($qty=null, $product)
    {
        $cfgId = $product->getId();
        
        $product->getTypeInstance(true)->setStoreFilter($product->getStore(), $product);
        $attributes = $product->getTypeInstance(true)->getConfigurableAttributes($product);
        $selectedAttributes = array();
        if ($product->getCustomOption('attributes')) {
            $selectedAttributes = unserialize($product->getCustomOption('attributes')->getValue());
        }
        $db = Mage::getSingleton('core/resource')->getConnection('core_read');
        $dbMeta = Mage::getSingleton('core/resource');

$sql = <<<SQL
    SELECT main_table.entity_id FROM {$dbMeta->getTableName('catalog/product')} `main_table` INNER JOIN {$dbMeta->getTableName('catalog/product_super_link')} `sl` ON sl.parent_id = {$cfgId} 
SQL;

        foreach($selectedAttributes as $attributeId => $optionId) {
            $alias = "a{$attributeId}";
            $sql .= ' INNER JOIN ' . $dbMeta->getTableName('catalog/product') . "_int" . " $alias ON $alias.entity_id = main_table.entity_id AND $alias.attribute_id = $attributeId AND $alias.value = $optionId AND $alias.entity_id = sl.product_id";
        }
        $id = $db->fetchOne($sql);
        
        Mage::log('id simple -->'.$id , null, 'confPricing.log');
        Mage::log($sql , null, 'confPricing.log');
        Mage::log('pid-->'.$cfgId , null, 'confPricing.log');
        Mage::log(Mage::getModel("catalog/product")->load($id)->getFinalPrice($qty), null, 'confPricing.log');
        //return
        $fp = Mage::getModel("catalog/product")->load($id)->getFinalPrice($qty);
        return $product->setFinalPrice($fp);
    }
}
