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
* @copyright  Copyright (c) 2014-2015 BssCommerce Co. (http://bsscommerce.com)
* @license    http://bsscommerce.com/Bss-Commerce-License.txt
*/
$installer = Mage::getResourceModel('catalog/setup', 'catalog_setup');
$installer->removeAttribute('catalog_product','pro_configurable_view');

$installer->startSetup();

$attributeName  = 'Pro Configurable View';
$attributeCode  = 'pro_configurable_view';
$attributeGroup = 'General';

$data = array(
    'type'      => 'int',
    'input'     => 'select',
    'global'    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'source'    => 'configurablegridview/source_option',
    'required'  => false,
    'user_defined' => true,
    'searchable' => false,
    'filterable' => false,
    'comparable' => false,
    'visible_on_front' => false,
    'unique' => false,
    'used_in_product_listing' => false,
    'label' => $attributeName,
    'default' => 0,
    'apply_to' => 'configurable',
);

$installer->addAttribute('catalog_product', $attributeCode, $data);
$entity = Mage_Catalog_Model_Product::ENTITY;
$attributeSetIds = $installer->getAllAttributeSetIds($entity);
foreach($attributeSetIds as $attributeSetId)
{
    $installer->addAttributeToGroup('catalog_product', $attributeSetId, $attributeGroup, $attributeCode);
}

$installer->endSetup();