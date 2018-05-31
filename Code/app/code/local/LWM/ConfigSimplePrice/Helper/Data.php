<?php

class LWM_ConfigSimplePrice_Helper_Data extends Mage_Core_Helper_Abstract
{
    /*
     * Custom Image Resize
     */
    public function resizeImg($fileName, $width, $height = '')
    {

        $folderURL = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog'.DS.'category'.DS.trim($fileName);
        $basePath = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS.'catalog'.DS.'category'.DS.trim($fileName);
        $newPath = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . "resized" . DS . $fileName;

        if ($width != '') {

            if (file_exists($basePath) && is_file($basePath) && !file_exists($newPath)) {
                $imageObj = new Varien_Image($basePath);
                $imageObj->constrainOnly(false);
                // $imageObj->keepAspectRatio(true);
                $imageObj->keepFrame(true);
                $imageObj->backgroundColor(array(255, 255, 255));
                $imageObj->resize($width, $height);
                $imageObj->save($newPath);
            }
            $resizedURL = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . "resized" . DS . $fileName;
        } else {
            $resizedURL = $folderURL;
        }
        return $resizedURL;
    }
    
}