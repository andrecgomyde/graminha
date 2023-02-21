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
* @package    Bss_Configurablegridview
* @author     Extension Team
* @copyright  Copyright (c) 2014-2016 BssCommerce Co. (http://bsscommerce.com)
* @license    http://bsscommerce.com/Bss-Commerce-License.txt
*/
class Bss_Configurablegridview_Model_Catalog_Product_Type_Configurable extends Mage_Catalog_Model_Product_Type_Configurable {
	public function processFileQueue() {
		if(!Mage::helper('configurablegridview')->getSetting('is_enabled')) {
			return parent::processFileQueue();
		}
		
        if (empty($this->_fileQueue)) {
            return $this;
        }

        foreach ($this->_fileQueue as &$queueOptions) {
            if (isset($queueOptions['operation']) && $operation = $queueOptions['operation']) {
                switch ($operation) {
                    case 'receive_uploaded_file':
                        $src = isset($queueOptions['src_name']) ? $queueOptions['src_name'] : '';
                        $dst = isset($queueOptions['dst_name']) ? $queueOptions['dst_name'] : '';
                        /** @var $uploader Zend_File_Transfer_Adapter_Http */
                        $uploader = isset($queueOptions['uploader']) ? $queueOptions['uploader'] : null;

                        $path = dirname($dst);
                        $io = new Varien_Io_File();
                        if (!$io->isWriteable($path) && !$io->mkdir($path, 0777, true)) {
                            Mage::throwException(Mage::helper('catalog')->__("Cannot create writeable directory '%s'.", $path));
                        }

                        $uploader->setDestination($path);
                        if(Mage::app()->getRequest()->getParam('configurable_grid_table') != 'Yes' || $queueOptions['configurable_file_last'] == 1) {
                            if (empty($src) || empty($dst) || !$uploader->receive($src)) {
                                /**
                                 * @todo: show invalid option
                                 */
                                if (isset($queueOptions['option'])) {
                                    $queueOptions['option']->setIsValid(false);
                                }
                                Mage::throwException(Mage::helper('catalog')->__("File upload failed"));
                            }
                            
                            Mage::helper('core/file_storage_database')->saveFile($dst);
                        }
                        
                        break;
                    case 'move_uploaded_file':
                        $src = $queueOptions['src_name'];
                        $dst = $queueOptions['dst_name'];
                        move_uploaded_file($src, $dst);
                        Mage::helper('core/file_storage_database')->saveFile($dst);
                        break;
                    default:
                        break;
                }
            }
            $queueOptions = null;
        }

        return $this;
    }
}