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
class Bss_Configurablegridview_Block_Checkout_Cart_Item_Configure extends Mage_Checkout_Block_Cart_Item_Configure
{
    protected function _prepareLayout()
    {
        // Set custom submit url route for form - to submit updated options to cart
        $block = $this->getLayout()->getBlock('product.info');
        if ($block) {
             $block->setSubmitRouteData(array(
                'route' => 'checkout/cart/updateItemOptions',
                'params' => array('id' => $this->getRequest()->getParam('id'))
             ));
        }

        // Set custom template with 'Update Cart' button
        $block = $this->getLayout()->getBlock('product.info.addtocart');
        if ($block) {
            // $block->setTemplate('checkout/cart/item/configure/updatecart.phtml');
            $block->setTemplate('bss/configurablegridview/addtocart.phtml');
        }

        return Mage_Core_Block_Template::_prepareLayout();
    }
}