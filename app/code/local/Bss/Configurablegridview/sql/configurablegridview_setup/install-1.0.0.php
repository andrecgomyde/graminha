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
$installer = $this;
$installer->startSetup();
 
/*$table = $installer->getConnection()
    ->newTable($installer->getTable('configurablegridview/product_price_indexer_final_idx'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Entity Id')
    ->addColumn('customer_group_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'unsigned'  => true,
        ), 'Customer Group Id')
    ->addColumn('website_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'unsigned'  => true,
        ), 'Website Id')
    ->addColumn('tax_class_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => true,
        'unsigned'  => true,
        'default'   => 0,
        ), 'Tax Class Id')
    ->addColumn('orig_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, null, array(
        'nullable'  => true,
        'precision' => 12,
        'scale'     => 4,
        'default'   => null,
        ), 'Original Price')
    ->addColumn('price', Varien_Db_Ddl_Table::TYPE_DECIMAL, null, array(
        'nullable'  => true,
        'precision' => 12,
        'scale'     => 4,
        'default'   => null,
        ), 'Price')
    ->addColumn('min_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, null, array(
        'nullable'  => true,
        'precision' => 12,
        'scale'     => 4,
        'default'   => null,
        ), 'Min Price')
    ->addColumn('max_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, null, array(
        'nullable'  => true,
        'precision' => 12,
        'scale'     => 4,
        'default'   => null,
        ), 'Max Price')
    ->addColumn('tier_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, null, array(
        'nullable'  => true,
        'precision' => 12,
        'scale'     => 4,
        'default'   => null,
        ), 'Tier Price')
    ->addColumn('base_tier', Varien_Db_Ddl_Table::TYPE_DECIMAL, null, array(
        'nullable'  => true,
        'precision' => 12,
        'scale'     => 4,
        'default'   => null,
        ), 'Base Tier Price')
    ->addColumn('group_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, null, array(
        'nullable'  => true,
        'precision' => 12,
        'scale'     => 4,
        'default'   => null,
        ), 'Group Price')
    ->addColumn('base_group_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, null, array(
        'nullable'  => true,
        'precision' => 12,
        'scale'     => 4,
        'default'   => null,
        ), 'Base Group Price');
$installer->getConnection()->createTable($table);
 
$table = $installer->getConnection()
    ->newTable($installer->getTable('configurablegridview/product_price_indexer_final_tmp'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Entity Id')
    ->addColumn('customer_group_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'unsigned'  => true,
        ), 'Customer Group Id')
    ->addColumn('website_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'unsigned'  => true,
        ), 'Website Id')
    ->addColumn('tax_class_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => true,
        'unsigned'  => true,
        'default'   => 0,
        ), 'Tax Class Id')
    ->addColumn('orig_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, null, array(
        'nullable'  => true,
        'precision' => 12,
        'scale'     => 4,
        'default'   => null,
        ), 'Original Price')
    ->addColumn('price', Varien_Db_Ddl_Table::TYPE_DECIMAL, null, array(
        'nullable'  => true,
        'precision' => 12,
        'scale'     => 4,
        'default'   => null,
        ), 'Price')
    ->addColumn('min_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, null, array(
        'nullable'  => true,
        'precision' => 12,
        'scale'     => 4,
        'default'   => null,
        ), 'Min Price')
    ->addColumn('max_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, null, array(
        'nullable'  => true,
        'precision' => 12,
        'scale'     => 4,
        'default'   => null,
        ), 'Max Price')
    ->addColumn('tier_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, null, array(
        'nullable'  => true,
        'precision' => 12,
        'scale'     => 4,
        'default'   => null,
        ), 'Tier Price')
    ->addColumn('base_tier', Varien_Db_Ddl_Table::TYPE_DECIMAL, null, array(
        'nullable'  => true,
        'precision' => 12,
        'scale'     => 4,
        'default'   => null,
        ), 'Base Tier Price')
    ->addColumn('group_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, null, array(
        'nullable'  => true,
        'precision' => 12,
        'scale'     => 4,
        'default'   => null,
        ), 'Group Price')
    ->addColumn('base_group_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, null, array(
        'nullable'  => true,
        'precision' => 12,
        'scale'     => 4,
        'default'   => null,
        ), 'Base Group Price');
$installer->getConnection()->createTable($table);*/

$installer->run("
-- DROP TABLE IF EXISTS `{$installer->getTable('configurablegridview/product_price_indexer_final_tmp')}`;
CREATE TABLE `{$installer->getTable('configurablegridview/product_price_indexer_final_tmp')}` (
  `entity_id` int(10) UNSIGNED NOT NULL COMMENT 'Entity ID',
  `customer_group_id` smallint(5) UNSIGNED NOT NULL COMMENT 'Customer Group ID',
  `website_id` smallint(5) UNSIGNED NOT NULL COMMENT 'Website ID',
  `tax_class_id` smallint(5) UNSIGNED DEFAULT '0' COMMENT 'Tax Class ID',
  `orig_price` decimal(12,4) DEFAULT NULL COMMENT 'Original Price',
  `price` decimal(12,4) DEFAULT NULL COMMENT 'Price',
  `min_price` decimal(12,4) DEFAULT NULL COMMENT 'Min Price',
  `max_price` decimal(12,4) DEFAULT NULL COMMENT 'Max Price',
  `tier_price` decimal(12,4) DEFAULT NULL COMMENT 'Tier Price',
  `base_tier` decimal(12,4) DEFAULT NULL COMMENT 'Base Tier',
  `group_price` decimal(12,4) DEFAULT NULL COMMENT 'Group price',
  `base_group_price` decimal(12,4) DEFAULT NULL COMMENT 'Base Group Price'
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COMMENT='Catalog Configurable Child Product Price Indexer Final Temp Table';

ALTER TABLE `{$installer->getTable('configurablegridview/product_price_indexer_final_tmp')}`
  ADD PRIMARY KEY (`entity_id`,`customer_group_id`,`website_id`);

-- DROP TABLE IF EXISTS `{$installer->getTable('configurablegridview/product_price_indexer_final_idx')}`;
CREATE TABLE `{$installer->getTable('configurablegridview/product_price_indexer_final_idx')}` (
  `entity_id` int(10) UNSIGNED NOT NULL COMMENT 'Entity ID',
  `customer_group_id` smallint(5) UNSIGNED NOT NULL COMMENT 'Customer Group ID',
  `website_id` smallint(5) UNSIGNED NOT NULL COMMENT 'Website ID',
  `tax_class_id` smallint(5) UNSIGNED DEFAULT '0' COMMENT 'Tax Class ID',
  `orig_price` decimal(12,4) DEFAULT NULL COMMENT 'Original Price',
  `price` decimal(12,4) DEFAULT NULL COMMENT 'Price',
  `min_price` decimal(12,4) DEFAULT NULL COMMENT 'Min Price',
  `max_price` decimal(12,4) DEFAULT NULL COMMENT 'Max Price',
  `tier_price` decimal(12,4) DEFAULT NULL COMMENT 'Tier Price',
  `base_tier` decimal(12,4) DEFAULT NULL COMMENT 'Base Tier',
  `group_price` decimal(12,4) DEFAULT NULL COMMENT 'Group price',
  `base_group_price` decimal(12,4) DEFAULT NULL COMMENT 'Base Group Price'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Catalog Configurable Child Product Price Indexer Final Index Table';

");

$installer->endSetup();