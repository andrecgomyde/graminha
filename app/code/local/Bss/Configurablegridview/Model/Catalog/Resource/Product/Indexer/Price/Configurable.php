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
class Bss_Configurablegridview_Model_Catalog_Resource_Product_Indexer_Price_Configurable extends Mage_Catalog_Model_Resource_Product_Indexer_Price_Configurable {
    /**
     * Reindex temporary (price result data) for all products
     *
     * @return Mage_Catalog_Model_Resource_Product_Indexer_Price_Configurable
     */
    public function reindexAll()
    {
        if(!Mage::helper('configurablegridview')->getSetting('is_enabled') || !Mage::helper('configurablegridview')->getSetting('use_simple_price')){
            return parent::reindexAll();
        }
        $this->useIdxTable(true);
        $this->beginTransaction();
        try {
            $this->_prepareFinalPriceData();
            $this->_prepareChildFinalPriceData();
            $this->_applyCustomOption();
            $this->_applyConfigurableOption();
            $this->_movePriceDataToIndexTable();
            $this->commit();
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
        return $this;
    }

    /**
     * Reindex temporary (price result data) for defined product(s)
     *
     * @param int|array $entityIds
     * @return Mage_Catalog_Model_Resource_Product_Indexer_Price_Configurable
     */
    public function reindexEntity($entityIds)
    {
        if(!Mage::helper('configurablegridview')->getSetting('is_enabled') || !Mage::helper('configurablegridview')->getSetting('use_simple_price')){
            return parent::reindexEntity($entityIds);
        }
        $read   = $this->_getReadAdapter();
        $select = $read->select()
                    ->from(array('spl' => $this->getTable('catalog/product_super_link')), array('product_id'))
                    ->where('parent_id IN (?)', $entityIds);

        $result = $read->raw_query($select)->fetchAll();

        if(count($result) > 0) {
            $childIds = array();
            foreach ($result as $id) {
                $childIds[] = $id['product_id'];
            }
            $this->_prepareChildFinalPriceData($childIds);
        }

        //var_dump($entityIds); die;

        $this->_prepareFinalPriceData($entityIds);
        $this->_applyCustomOption();
        $this->_applyConfigurableOption();
        $this->_movePriceDataToIndexTable();

        return $this;
    }

    /**
     * Prepare child products default final price in temporary index table
     *
     * @param int|array $entityIds  the entity ids limitation
     * @return Mage_Catalog_Model_Resource_Product_Indexer_Price_Default
     */
    protected function _prepareChildFinalPriceData($entityIds = null)
    {
        $this->_prepareDefaultChildFinalPriceTable();

        $write  = $this->_getWriteAdapter();
        $select = $write->select()
            ->from(array('e' => $this->getTable('catalog/product')), array('entity_id'))
            ->joinLeft(
                array('l' => $this->getTable('catalog/product_super_link')),
                'l.product_id = e.entity_id',
                array())
            ->join(
                array('cg' => $this->getTable('customer/customer_group')),
                '',
                array('customer_group_id'))
            ->join(
                array('cw' => $this->getTable('core/website')),
                '',
                array('website_id'))
            ->join(
                array('cwd' => $this->_getWebsiteDateTable()),
                'cw.website_id = cwd.website_id',
                array())
            ->join(
                array('csg' => $this->getTable('core/store_group')),
                'csg.website_id = cw.website_id AND cw.default_group_id = csg.group_id',
                array())
            ->join(
                array('cs' => $this->getTable('core/store')),
                'csg.default_store_id = cs.store_id AND cs.store_id != 0',
                array())
            ->join(
                array('pw' => $this->getTable('catalog/product_website')),
                'pw.product_id = e.entity_id AND pw.website_id = cw.website_id',
                array())
            ->joinLeft(
                array('tp' => $this->_getTierPriceIndexTable()),
                'tp.entity_id = e.entity_id AND tp.website_id = cw.website_id'
                    . ' AND tp.customer_group_id = cg.customer_group_id',
                array())
            ->joinLeft(
                array('gp' => $this->_getGroupPriceIndexTable()),
                'gp.entity_id = e.entity_id AND gp.website_id = cw.website_id'
                    . ' AND gp.customer_group_id = cg.customer_group_id',
                array())
            ->where('e.type_id = ?', 'simple');

        // add enable products limitation
        $statusCond = $write->quoteInto('=?', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $this->_addAttributeToSelect($select, 'status', 'e.entity_id', 'cs.store_id', $statusCond, true);
        if (Mage::helper('core')->isModuleEnabled('Mage_Tax')) {
            $taxClassId = $this->_addAttributeToSelect($select, 'tax_class_id', 'e.entity_id', 'cs.store_id');
        } else {
            $taxClassId = new Zend_Db_Expr('0');
        }
        $select->columns(array('tax_class_id' => $taxClassId));

        $price          = $this->_addAttributeToSelect($select, 'price', 'e.entity_id', 'cs.store_id');
        $specialPrice   = $this->_addAttributeToSelect($select, 'special_price', 'e.entity_id', 'cs.store_id');
        $specialFrom    = $this->_addAttributeToSelect($select, 'special_from_date', 'e.entity_id', 'cs.store_id');
        $specialTo      = $this->_addAttributeToSelect($select, 'special_to_date', 'e.entity_id', 'cs.store_id');
        $currentDate    = $write->getDatePartSql('cwd.website_date');
        $groupPrice     = $write->getCheckSql('gp.price IS NULL', "{$price}", 'gp.price');

        $specialFromDate    = $write->getDatePartSql($specialFrom);
        $specialToDate      = $write->getDatePartSql($specialTo);

        $specialFromUse     = $write->getCheckSql("{$specialFromDate} <= {$currentDate}", '1', '0');
        $specialToUse       = $write->getCheckSql("{$specialToDate} >= {$currentDate}", '1', '0');
        $specialFromHas     = $write->getCheckSql("{$specialFrom} IS NULL", '1', "{$specialFromUse}");
        $specialToHas       = $write->getCheckSql("{$specialTo} IS NULL", '1', "{$specialToUse}");
        $finalPrice         = $write->getCheckSql("{$specialFromHas} > 0 AND {$specialToHas} > 0"
            . " AND {$specialPrice} < {$price}", $specialPrice, $price);
        $finalPrice         = $write->getCheckSql("{$groupPrice} < {$finalPrice}", $groupPrice, $finalPrice);

        $select->columns(array(
            'orig_price'       => $price,
            'price'            => $finalPrice,
            'min_price'        => $finalPrice,
            'max_price'        => $finalPrice,
            'tier_price'       => new Zend_Db_Expr('tp.min_price'),
            'base_tier'        => new Zend_Db_Expr('tp.min_price'),
            'group_price'      => new Zend_Db_Expr('gp.price'),
            'base_group_price' => new Zend_Db_Expr('gp.price'),
        ));

        if (!is_null($entityIds)) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        /**
         * Add additional external limitation
         */
        Mage::dispatchEvent('prepare_catalog_product_index_select', array(
            'select'        => $select,
            'entity_field'  => new Zend_Db_Expr('e.entity_id'),
            'website_field' => new Zend_Db_Expr('cw.website_id'),
            'store_field'   => new Zend_Db_Expr('cs.store_id')
        ));

        $query = $select->insertFromSelect($this->_getDefaultChildFinalPriceTable(), array(), false);
        $write->query($query);

        /**
         * Add possibility modify prices from external events
         */
        $select = $write->select()
            ->join(array('wd' => $this->_getWebsiteDateTable()),
                'i.website_id = wd.website_id',
                array());
        Mage::dispatchEvent('prepare_catalog_product_price_index_table', array(
            'index_table'       => array('i' => $this->_getDefaultChildFinalPriceTable()),
            'select'            => $select,
            'entity_id'         => 'i.entity_id',
            'customer_group_id' => 'i.customer_group_id',
            'website_id'        => 'i.website_id',
            'website_date'      => 'wd.website_date',
            'update_fields'     => array('price', 'min_price', 'max_price')
        ));

        return $this;
    }

    /**
     * Calculate minimal and maximal prices for configurable product options
     * and apply it to final price
     *
     * @return Mage_Catalog_Model_Resource_Product_Indexer_Price_Configurable
     */
    protected function _applyConfigurableOption()
    {
        if(!Mage::helper('configurablegridview')->getSetting('is_enabled') || !Mage::helper('configurablegridview')->getSetting('use_simple_price')){
            return parent::_applyConfigurableOption();
        }
        $write      = $this->_getWriteAdapter();
        $coaTable   = $this->_getConfigurableOptionAggregateTable();
        $copTable   = $this->_getConfigurableOptionPriceTable();

        $this->_prepareConfigurableOptionAggregateTable();
        $this->_prepareConfigurableOptionPriceTable();

        $select = $write->select()
            ->from(array('i' => $this->_getDefaultFinalPriceTable()), array())
            ->join(
                array('l' => $this->getTable('catalog/product_super_link')),
                'l.parent_id = i.entity_id',
                array('parent_id', 'product_id'))
            ->columns(array('customer_group_id', 'website_id'), 'i')
            ->join(
                array('a' => $this->getTable('catalog/product_super_attribute')),
                'l.parent_id = a.product_id',
                array())
            ->join(
                array('cp' => $this->getValueTable('catalog/product', 'int')),
                'l.product_id = cp.entity_id AND cp.attribute_id = a.attribute_id AND cp.store_id = 0',
                array())
            ->joinLeft(
                array('apd' => $this->getTable('catalog/product_super_attribute_pricing')),
                'a.product_super_attribute_id = apd.product_super_attribute_id'
                    . ' AND apd.website_id = 0 AND cp.value = apd.value_index',
                array())
            ->joinLeft(
                array('apw' => $this->getTable('catalog/product_super_attribute_pricing')),
                'a.product_super_attribute_id = apw.product_super_attribute_id'
                    . ' AND apw.website_id = i.website_id AND cp.value = apw.value_index',
                array())
            ->join(
                array('le' => $this->getTable('catalog/product')),
                'le.entity_id = l.product_id',
                array())
            ->where('le.required_options=0')
            ->group(array('l.parent_id', 'i.customer_group_id', 'i.website_id', 'l.product_id'));
        $this->_addWebsiteJoinToSelect($select, true);
        $this->_addProductWebsiteJoinToSelect($select, 'cw.website_id', 'le.entity_id');

        $priceExpression = $write->getCheckSql('apw.value_id IS NOT NULL', 'apw.pricing_value', 'apd.pricing_value');
        $percentExpr = $write->getCheckSql('apw.value_id IS NOT NULL', 'apw.is_percent', 'apd.is_percent');
        $roundExpr = "ROUND(i.price * ({$priceExpression} / 100), 4)";
        $roundPriceExpr = $write->getCheckSql("{$percentExpr} = 1", $roundExpr, $priceExpression);
        $priceColumn = $write->getCheckSql("{$priceExpression} IS NULL", '0', $roundPriceExpr);
        $priceColumn = new Zend_Db_Expr("SUM({$priceColumn})");

        $tierPrice = $priceExpression;
        $tierRoundPriceExp = $write->getCheckSql("{$percentExpr} = 1", $roundExpr, $tierPrice);
        $tierPriceExp = $write->getCheckSql("{$tierPrice} IS NULL", '0', $tierRoundPriceExp);
        $tierPriceColumn = $write->getCheckSql("MIN(i.tier_price) IS NOT NULL", "SUM({$tierPriceExp})", 'NULL');

        $groupPrice = $priceExpression;
        $groupRoundPriceExp = $write->getCheckSql("{$percentExpr} = 1", $roundExpr, $groupPrice);
        $groupPriceExp = $write->getCheckSql("{$groupPrice} IS NULL", '0', $groupRoundPriceExp);
        $groupPriceColumn = $write->getCheckSql("MIN(i.group_price) IS NOT NULL", "SUM({$groupPriceExp})", 'NULL');

        $select->columns(array(
            'price'       => $priceColumn,
            'tier_price'  => $tierPriceColumn,
            'group_price' => $groupPriceColumn,
        ));

        $statusCond = $write->quoteInto(' = ?', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $this->_addAttributeToSelect($select, 'status', 'le.entity_id', 'cs.store_id', $statusCond);

        $query = $select->insertFromSelect($coaTable);
        $write->query($query);

        $select = $write->select()
            ->from(array('coa' => $coaTable), array(
                    'coa.parent_id', 'coa.customer_group_id', 'coa.website_id',
                    'MIN(cfp.min_price)', 'MAX(cfp.max_price)', 'MIN(coa.tier_price)', 'MIN(coa.group_price)'
                ))
            ->joinLeft(
                array('cfp' => $this->getTable('configurablegridview/product_price_indexer_final_tmp')),
                'cfp.entity_id = coa.child_id'
                    . ' AND cfp.customer_group_id = coa.customer_group_id AND cfp.website_id = coa.website_id',
                array())
            ->group(array('coa.parent_id', 'coa.customer_group_id', 'coa.website_id'));

        $query = $select->insertFromSelect($copTable);
        $write->query($query);

        $table  = array('i' => $this->_getDefaultFinalPriceTable());
        $select = $write->select()
            ->join(
                array('io' => $copTable),
                'i.entity_id = io.entity_id AND i.customer_group_id = io.customer_group_id'
                    .' AND i.website_id = io.website_id',
                array());
        $select->columns(array(
            'min_price'   => new Zend_Db_Expr('io.min_price'),
            'max_price'   => new Zend_Db_Expr('io.max_price'),
            'tier_price'  => $write->getCheckSql('i.tier_price IS NOT NULL', 'i.tier_price + io.tier_price', 'NULL'),
            'group_price' => $write->getCheckSql(
                'i.group_price IS NOT NULL',
                'i.group_price + io.group_price', 'NULL'
            ),
        ));

        $query = $select->crossUpdateFromSelect($table);
        $write->query($query);

        $select = $write->select()
            ->from($table)
            ->join(
                array('e' => $this->getTable('catalog/product')),
                'e.entity_id = i.entity_id',
                array())
            ->joinLeft(
                array('coa' => $coaTable),
                'coa.parent_id = i.entity_id',
                array())
            ->where('e.type_id = ?', $this->getTypeId())
            ->where('coa.parent_id IS NULL');

        $query = $select->deleteFromSelect('i');
        $write->query($query);

        $write->delete($coaTable);
        $write->delete($copTable);
        
        return $this;
    }

    protected function _prepareDefaultChildFinalPriceTable() {
        $this->_getWriteAdapter()->delete($this->_getDefaultChildFinalPriceTable());
        return $this;
    }

    protected function _getDefaultChildFinalPriceTable() {
        if ($this->useIdxTable()) {
            return $this->getTable('configurablegridview/product_price_indexer_final_idx');
        }
        return $this->getTable('configurablegridview/product_price_indexer_final_tmp');
    }
}