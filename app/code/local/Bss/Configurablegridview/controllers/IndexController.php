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
class Bss_Configurablegridview_IndexController extends Mage_Core_Controller_Front_Action {

  public function IndexAction() {
  	$childId = (int) $this->getRequest()->getParam('id');
    $this->loadLayout();
  	$block =  $this->getLayout()->createBlock('catalog/product_view_media')->setTemplate('catalog/product/view/media.phtml')->setProduct(Mage::getModel('catalog/product')->load($childId))->toHtml();
   	$this->getResponse()->setBody($block);
  }
}