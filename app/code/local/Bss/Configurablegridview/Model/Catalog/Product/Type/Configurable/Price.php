<?php
/**
* BssCommerce Co.
*
* NOTICE OF LICENSE
*
* This source file is subject to the EULA
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://bsscommerce.com/Bss-Commerce-License.txt
*
* =================================================================
*                 MAGENTO EDITION USAGE NOTICE
* =================================================================
* This package designed for Magento COMMUNITY edition
* BssCommerce does not guarantee correct work of this extension
* on any other Magento edition except Magento COMMUNITY edition.
* BssCommerce does not provide extension support in case of
* incorrect edition usage.
* =================================================================
*
* @category   BSS
* @package    BSS_Configurablegridview
* @author     Hieu Dang
* @copyright  Copyright (c) 2014-2105 BssCommerce Co. (http://bsscommerce.com)
* @license    http://bsscommerce.com/Bss-Commerce-License.txt
*/
class Bss_Configurablegridview_Model_Catalog_Product_Type_Configurable_Price extends Mage_Catalog_Model_Product_Type_Configurable_Price
{
	public function getFinalPrice($qty=null, $product)
    {
    	$useSimple = Mage::helper('configurablegridview')->getSetting('use_simple_price');
    	$isEnabled = Mage::helper('configurablegridview')->getSetting('is_enabled');
    	$productCol = Mage::getModel('catalog/product')->load($product->getId()); 
    	if($useSimple && $isEnabled && ($productCol->getProConfigurableView() == null || $productCol->getProConfigurableView() == Bss_Configurablegridview_Helper_Data::PRODUCT_PRO_VIEW_ENABLED)){
	    	$selectedAttributes = array();
		    if ($product->getCustomOption('attributes')) {
		        $selectedAttributes = unserialize($product->getCustomOption('attributes')->getValue());
		    }
		    if (sizeof($selectedAttributes)){
		    	$finalPrice = $this->getSimpleProductPrice($qty, $product);
		    	// $finalPrice += $this->getTotalConfigurableItemsPrice($product, $finalPrice);
		        $finalPrice += $this->_applyOptionsPrice($product, $qty, $finalPrice) - $finalPrice;
		        $finalPrice = max(0, $finalPrice);
		    	return $finalPrice;
		    }
		}else{
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
    }

    public function getSimpleProductPrice($qty=null, $product)
    {   	
        $product->getTypeInstance(true)
            ->setStoreFilter($product->getStore(), $product);
        $attributes = $product->getTypeInstance(true)
            ->getConfigurableAttributes($product);
        $selectedAttributes = array();
        if ($product->getCustomOption('attributes')) {
            $selectedAttributes = unserialize($product->getCustomOption('attributes')->getValue());
        }
        $simpleId = $this->getIdSimple($product, $selectedAttributes);
        $productChild = Mage::getModel("catalog/product")->load($simpleId);
        if (Mage::app()->getStore()->isAdmin()) {
        	$customerGroup = $this->_getCustomerGroupId($product);
	        if ($customerGroup) {
	        	$productChild->setCustomerGroupId($customerGroup);
	        }
	    }
        
        return $productChild->getFinalPrice($qty);
    }

    public function getIdSimple($product, $selectedAttributes){
		$childProduct = Mage::getModel('catalog/product_type_configurable')->getProductByAttributes($selectedAttributes, $product);
		$childId = $childProduct->getId();
		return $childId;
	}
}
		