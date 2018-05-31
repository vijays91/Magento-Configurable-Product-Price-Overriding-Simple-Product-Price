<?php
/*
 *
 *
 *
 */
class LWM_ConfigSimplePrice_Model_Product_Type_Configurable_Price extends Mage_Catalog_Model_Product_Type_Configurable_Price
{
 
    public function getFinalPrice($qty=null, $product)
    {
        // Mage::log('enter final price-----', null, 'resimconfig.log');
        //Start edit
        $selectedAttributes = array();
        if ($product->getCustomOption('attributes')) {
            $selectedAttributes = unserialize($product->getCustomOption('attributes')->getValue());
        }
        if (sizeof($selectedAttributes)) {
            return $this->getSimpleProductPrice($qty, $product);
        }
        //End edit

        if (is_null($qty) && !is_null($product->getCalculatedFinalPrice())) {
            return $product->getCalculatedFinalPrice();
        }

        $basePrice = $this->getBasePrice($product, $qty);
        $finalPrice = $basePrice;
        $product->setFinalPrice($finalPrice);
        Mage::dispatchEvent('catalog_product_get_final_price', array('product' => $product, 'qty' => $qty));
        $finalPrice = $product->getData('final_price');

        $finalPrice += $this->getTotalConfigurableItemsPrice($product, $finalPrice);
        $finalPrice += $this->_applyOptionsPrice($product, $qty, $basePrice) - $basePrice;
        $finalPrice = max(0, $finalPrice);

        $product->setFinalPrice($finalPrice);
        return $finalPrice;
    }

    public function getSimpleProductPrice($qty=null, $product)
    {
        $cfgId = $product->getId();
        $product->getTypeInstance(true)
        ->setStoreFilter($product->getStore(), $product);
        $attributes = $product->getTypeInstance(true)
        ->getConfigurableAttributes($product);
        $selectedAttributes = array();
        if ($product->getCustomOption('attributes')) {
        $selectedAttributes = unserialize($product->getCustomOption('attributes')->getValue());
        }
        $db = Mage::getSingleton('core/resource')->getConnection('core_read');
        $dbMeta = Mage::getSingleton('core/resource');
$sql = <<<SQL
SELECT main_table.entity_id FROM {$dbMeta->getTableName('catalog/product')} `main_table` INNER JOIN
{$dbMeta->getTableName('catalog/product_super_link')} `sl` ON sl.parent_id = {$cfgId}
SQL;
        foreach($selectedAttributes as $attributeId => $optionId) {
        $alias = "a{$attributeId}";
        $sql .= ' INNER JOIN ' . $dbMeta->getTableName('catalog/product') . "_int" . " $alias ON $alias.entity_id = main_table.entity_id AND $alias.attribute_id = $attributeId AND $alias.value = $optionId AND $alias.entity_id = sl.product_id";
        }
        $id = $db->fetchOne($sql);
        return Mage::getModel("catalog/product")->load($id)->getFinalPrice($qty);
    }
}
