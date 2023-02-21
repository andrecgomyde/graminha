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
require_once 'Mage/Checkout/controllers/CartController.php';
class Bss_Configurablegridview_CartController extends Mage_Checkout_CartController {
  public function addAction() {
    if($this->getRequest()->getParam('configurable_grid_table') == 'Yes') {
      try {
        $params = $this->getRequest()->getParams();
        $config_super_attributes = $params['super_attribute_quickshop'];
        $cart   = $this->_getCart();
        $config_table_qty = $params['config_table_qty'];
        $options = isset($params['options']) ? $params['options'] : null;
        $qty_config = array();
        foreach($config_table_qty as $sup_qty => $_super_qty) {
            $qty_config[$sup_qty] =$_super_qty;
            $required += $_super_qty;
        }
        if($required == 0) {
          $this->_getSession()->addError($this->__('Cannot add the item to shopping cart.'));
          $this->_goBack();
          return;
        }
		
		$c = 0;
		foreach($config_super_attributes as $sId => $config_attribute) {
			if(isset($config_table_qty[$sId]) && $config_table_qty[$sId]!='' && $config_table_qty[$sId] > 0) {
				$c++;
			}
		}
				
        $config_table_qty = $qty_config;
		
		$i = 1;
        foreach($config_super_attributes as $sId => $config_attribute) {
          if(isset($config_table_qty[$sId]) && $config_table_qty[$sId]!='' && $config_table_qty[$sId] > 0) {
			if($i == $c) {
				Mage::register('configurable_file_last', 1);
			}
			$i++;
						
            $product= $this->_initProduct();
            $related= $this->getRequest()->getParam('related_product');
            if (!$product) {
              $this->_goBack();
              return;
            }
            if(isset($config_table_qty[$sId])) {
              $params2 = array();
              $params2['qty'] = $config_table_qty[$sId];
              $params2['super_attribute'] = $config_attribute;
              if($options != null) $params2['options'] = $options;
              if($params2['qty'] > 0 && $params2['qty']!='') {
                $cart->addProduct($product, $params2);
                if (!empty($related)) {
                  $cart->addProductsByIds(explode(',', $related));
                }
              }
            }
          }
        }
        $cart->save();
        // set the cart as updated
        Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
        $message = $this->__('%s was successfully added to your shopping cart.', $product['name']);
        if (!$this->_getSession()->getNoCartRedirect(true)) {
          $this->_getSession()->addSuccess($message);
          $this->_goBack();
        }
      } catch (Mage_Core_Exception $e) {
          if ($this->_getSession()->getUseNotice(true)) {
              $this->_getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
          } else {
              $messages = array_unique(explode("\n", $e->getMessage()));
              foreach ($messages as $message) {
                  $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
              }
          }
          $url = $this->_getSession()->getRedirectUrl(true);
          if ($url) {
              $this->getResponse()->setRedirect($url);
          } else {
              $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
          }
      } catch (Exception $e) {
          $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
          Mage::logException($e);
          $this->_goBack();
      }
    } else {
      return parent::addAction();
    }
     
  }
  public function updateItemOptionsAction(){
    if($this->getRequest()->getParam('configurable_grid_table') == 'Yes') {
      $cart   = $this->_getCart();     
      $paramsRes = $this->getRequest()->getParams();
      $config_super_attributes = $paramsRes['super_attribute_quickshop'];
      $config_table_qty = $paramsRes['config_table_qty'];
      $options = isset($paramsRes['options']) ? $paramsRes['options'] : null;
      $qty_config = array();
      foreach($config_table_qty as $sup_qty => $_super_qty) {
          $qty_config[$sup_qty] =$_super_qty;
          $required += $_super_qty;
      }
      if($required == 0) {
        $this->_getSession()->addError($this->__('Cannot add the item to shopping cart.'));
        $this->_goBack();
        return;
      }
      $config_table_qty = $qty_config;
      $config_table_item_id = $paramsRes['item_id'];
      foreach($config_table_item_id as $sup_item => $_super_item) {
          $item_config[$sup_item] =$_super_item;
      }
      $config_table_item = $item_config;

      foreach($config_super_attributes as $sId => $config_attribute) {
        if(isset($config_table_qty[$sId]) && $config_table_qty[$sId]!='') {
          $product= $this->_initProduct();
          $related= $this->getRequest()->getParam('related_product');
          if (!$product) {
            $this->_goBack();
            return;
          }
          if($config_table_item){
            $id = $config_table_item[$sId];
          }
          if($id && $config_table_qty[$sId] == 0){
            $cart->removeItem($id);
            continue;
          }
          if(isset($config_table_qty[$sId]) && $config_table_qty[$sId]!='' && $config_table_qty[$sId] > 0){
            $params = array();
            $params['qty'] = $config_table_qty[$sId];
            $params['super_attribute'] = $config_attribute;
            $params['options'] = $paramsRes['options'];
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            if(!$id){
              Mage::helper('checkout/cart')->getCart()->addProduct($product, $params);
              continue;
            }

            $quoteItem = $cart->getQuote()->getItemById($id);
            $params['id'] = $id;
            $params['product'] = $quoteItem->getProductId();
            
            if (!$quoteItem) {
                Mage::throwException($this->__('Quote item is not found.'));
            }

            try {
              $item = $cart->updateItem($id, new Varien_Object($params));
            } catch(Exception $e) {
              $this->_getSession()->addError($this->__($e->getMessage()));
              $this->_goBack();
              return;
            }
            
            if (is_string($item)) {
                Mage::throwException($item);
            }
            if ($item->getHasError()) {
                Mage::throwException($item->getMessage());
            }

            $related = $this->getRequest()->getParam('related_product');
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }
          }
        }
      }
      $cart->save();
      $this->_getSession()->setCartWasUpdated(true);
      if (!$this->_getSession()->getNoCartRedirect(true)) {
        if (!$cart->getQuote()->getHasError()) {
            $message = $this->__('%s was updated in your shopping cart.', $product['name']);
            $this->_getSession()->addSuccess($message);
        }
        $this->_goBack();
      }
    }else{
      return parent::updateItemOptionsAction();
    }
  }
  public function configureAction()
  {
      // Extract item and product to configure
      $id = (int) $this->getRequest()->getParam('id');
      $quoteItem = null;
      $cart = $this->_getCart();
      if ($id) {
          $quoteItem = $cart->getQuote()->getItemById($id);
      }

      if (!$quoteItem) {
          // $this->_getSession()->addError($this->__('Quote item is not found.'));
          $this->_redirect('checkout/cart');
          return;
      }

      try {
          $params = new Varien_Object();
          $params->setCategoryId(false);
          $params->setConfigureMode(true);
          $params->setBuyRequest($quoteItem->getBuyRequest());

          Mage::helper('catalog/product_view')->prepareAndRender($quoteItem->getProduct()->getId(), $this, $params);
      } catch (Exception $e) {
          $this->_getSession()->addError($this->__('Cannot configure product.'));
          Mage::logException($e);
          $this->_goBack();
          return;
      }
  }
}