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
class Bss_Configurablegridview_Model_System_Config_Backend_Custom extends Mage_Core_Model_Config_Data
{
    protected function _afterSave()
    {
        $change = false;
        if ($this->isValueChanged() && $this->getField() == 'is_enabled') {
            $group = $this->getGroups();
            if($group['settings']['fields']['use_simple_price']['value'] == 1) $change = true;
        }
        if($this->isValueChanged() && $this->getField() == 'use_simple_price') $change = true;
        if($change){
            Mage::getSingleton('index/indexer')->getProcessById(2)->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
        }
    }
}
