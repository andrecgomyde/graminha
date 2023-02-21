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
require_once "Mage/Catalog/controllers/ProductController.php";  
class Bss_Configurablegridview_Catalog_ProductController extends Mage_Catalog_ProductController{
    public function galleryAction()
    {
    	if(Mage::helper('configurablegridview')->getSetting('is_enabled') && Mage::helper('configurablegridview')->getSetting('option_swatch')){
	      	$product = Mage::getModel('catalog/product')->load($this->getRequest()->getParam('id'));
	        Mage::register('product', $product);
	        $this->loadLayout();
	        $this->renderLayout();
	    }else{
	    	return parent::galleryAction();
	    }
    }
}
				