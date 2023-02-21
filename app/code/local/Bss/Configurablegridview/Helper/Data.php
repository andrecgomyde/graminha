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
class Bss_Configurablegridview_Helper_Data extends Mage_Core_Helper_Abstract
{
	CONST PRODUCT_PRO_VIEW_ENABLED = 0;
	CONST PRODUCT_PRO_VIEW_DISABLED = 1;

	public function getSetting($field){
		return Mage::getStoreConfigFlag('configurablegridview/settings/'.$field);
	}

	public function getHasSpecialPrice($product){
		$customerGroup = Mage::getSingleton('customer/session')->getCustomerGroupId();
		$groupPrices = $product->getData('group_price');
		$groupPriceRes ='';
		$specialPriceRes = '';
		if($groupPrices){
			foreach ($groupPrices as $groupPrice) {
	            if($groupPrice['cust_group'] == $customerGroup && $groupPrice['price'] == $product->getFinalPrice()){
	            	$groupPriceRes = $groupPrice['price'];
	            	break;
	            }
	        }
	    }
		if($product->getSpecialPrice() && $product->getSpecialPrice() == $product->getFinalPrice()) $specialPriceRes = $product->getSpecialPrice();
		if($groupPriceRes && !$specialPriceRes) return $groupPriceRes;
		if(!$groupPriceRes && $specialPriceRes) return $specialPriceRes;
		return min(array($groupPriceRes,$specialPriceRes));
	}

	public function getTemplate() {
		if (Mage::registry('product')) {
			$product = Mage::registry('product');
			if(!$product->getProConfigurableView() || $product->getProConfigurableView() == self::PRODUCT_PRO_VIEW_ENABLED) {
				return 'bss/configurablegridview/type/configurable.phtml';
			}
		}
		return 'catalog/product/view/type/options/configurable.phtml';
	}

	public function getTemplatePrice() {
		if($this->getSetting('use_simple_price')){
			return 'bss/configurablegridview/catalog/product/price.phtml';
		}
		return 'catalog/product/price.phtml';
	}

	public function getAddToCartTemplate() {
		if (Mage::registry('product')) {
			$product = Mage::registry('product');
			if(!$product->getProConfigurableView() || $product->getProConfigurableView() == self::PRODUCT_PRO_VIEW_ENABLED) {
				return 'bss/configurablegridview/addtocart.phtml';
			}
		}
		
		return 'catalog/product/view/addtocart.phtml';
	}

	public function getChildrenInfo($product, $productAttributes) {
    	$associative_products = $product->getTypeInstance()->getUsedProducts();
		$assc_product_data = array();
		$labels = array();
		$options = array();
		$store = Mage::app()->getStore()->getId();
		foreach ($associative_products as $assc_products) {
			$storeIds = $assc_products->getStoreIds();
			if($assc_products->getStatus() == 1 && in_array($store, $storeIds)) {
				if(!$this->getSetting('show_out_stock') && !$assc_products->isSaleable()) continue;
				$inventory = Mage::getModel('cataloginventory/stock_item')->loadByProduct($assc_products);
				$stock = number_format($inventory->getQty());
				$status_stock = $assc_products->getStockItem();
				$instock = false;
				$backorder = false;
				if ($status_stock->getIsInStock()) {
					$instock = true;
				}
				if ($inventory->getBackorders()) {
					$backorder = true;
				}

				$assc_product_data[$assc_products->getId()]['info'] = array('price' => 0, 'qty' => $stock, 'prod_id'=>$assc_products->getId(), 'status_stock' => $instock, 'backorder' => $backorder);

				foreach ($productAttributes as $attribute) {

					$_attributePrice = $attribute->getPrices();

					$labels[$attribute->getLabel()] = $attribute->getLabel();

					$value = $assc_products->getResource()->getAttribute($attribute->getProductAttribute()->getAttributeCode())->getFrontend()->getValue($assc_products);
					$options[$value] = $value;
					$att_array = array('code' => $attribute->getProductAttribute()->getAttributeCode(), 'label' => $attribute->getLabel(), 'value' => $value, 'attribute_id' => $attribute->getAttributeId());

					foreach($_attributePrice as $optionVal){
						if($optionVal['label'] == $value){
							$att_array['option_id'] = $optionVal['value_index'];
							$att_array['pricing_value'] = $optionVal['pricing_value'];
							$att_array['is_percent'] = $optionVal['is_percent'];
							if(Mage::helper('core')->isModuleEnabled('Mage_ConfigurableSwatches') && Mage::helper('configurableswatches')->isEnabled()){
								$att_array['attr_img'] = Mage::helper('configurableswatches/productimg')->getSwatchUrl($assc_products, $att_array['value']);
							}
						}
					}
					$assc_product_data[$assc_products->getId()]['attributes'][] = $att_array;
				}
			}
		}
		$assc_product_data = $assc_product_data;
		$configurable_products = array('num_attributes' => count($productAttributes), 'products' => $assc_product_data, 'labels' => $labels, 'options' => $options);
		return serialize($configurable_products);
	}
}