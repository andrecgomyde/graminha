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
class Bss_Configurablegridview_Model_Catalog_Product_Option_Type_File extends Mage_Catalog_Model_Product_Option_Type_File {
	protected function _validateUploadedFile() {
		if(!Mage::helper('configurablegridview')->getSetting('is_enabled')) {
			return parent::_validateUploadedFile();
		}
		
        $option = $this->getOption();
        $processingParams = $this->_getProcessingParams();

        /**
         * Upload init
         */
        $upload   = new Zend_File_Transfer_Adapter_Http();
        $file = $processingParams->getFilesPrefix() . 'options_' . $option->getId() . '_file';
        try {
            $runValidation = $option->getIsRequire() || $upload->isUploaded($file);
            if (!$runValidation) {
                $this->setUserValue(null);
                return $this;
            }

            $fileInfo = $upload->getFileInfo($file);
            $fileInfo = $fileInfo[$file];
            $fileInfo['title'] = $fileInfo['name'];

        } catch (Exception $e) {
            // when file exceeds the upload_max_filesize, $_FILES is empty
            if (isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > $this->_getUploadMaxFilesize()) {
                $this->setIsValid(false);
                $value = $this->_bytesToMbytes($this->_getUploadMaxFilesize());
                Mage::throwException(
                    Mage::helper('catalog')->__("The file you uploaded is larger than %s Megabytes allowed by server", $value)
                );
            } else {
                switch($this->getProcessMode())
                {
                    case Mage_Catalog_Model_Product_Type_Abstract::PROCESS_MODE_FULL:
                        Mage::throwException(
                            Mage::helper('catalog')->__('Please specify the product\'s required option(s).')
                        );
                        break;
                    default:
                        $this->setUserValue(null);
                        break;
                }
                return $this;
            }
        }

        /**
         * Option Validations
         */

        // Image dimensions
        $_dimentions = array();
        if ($option->getImageSizeX() > 0) {
            $_dimentions['maxwidth'] = $option->getImageSizeX();
        }
        if ($option->getImageSizeY() > 0) {
            $_dimentions['maxheight'] = $option->getImageSizeY();
        }
        if (count($_dimentions) > 0) {
            $upload->addValidator('ImageSize', false, $_dimentions);
        }

        // File extension
        $_allowed = $this->_parseExtensionsString($option->getFileExtension());
        if ($_allowed !== null) {
            $upload->addValidator('Extension', false, $_allowed);
        } else {
            $_forbidden = $this->_parseExtensionsString($this->getConfigData('forbidden_extensions'));
            if ($_forbidden !== null) {
                $upload->addValidator('ExcludeExtension', false, $_forbidden);
            }
        }

        // Maximum filesize
        $upload->addValidator('FilesSize', false, array('max' => $this->_getUploadMaxFilesize()));

        /**
         * Upload process
         */

        $this->_initFilesystem();
        
        if ($upload->isUploaded($file) && $upload->isValid($file)) {

            $extension = pathinfo(strtolower($fileInfo['name']), PATHINFO_EXTENSION);

            $fileName = Mage_Core_Model_File_Uploader::getCorrectFileName($fileInfo['name']);
            $dispersion = Mage_Core_Model_File_Uploader::getDispretionPath($fileName);

            $filePath = $dispersion;
            $fileHash = md5(file_get_contents($fileInfo['tmp_name']));
            $filePath .= DS . $fileHash . '.' . $extension;
            $fileFullPath = $this->getQuoteTargetDir() . $filePath;

            $upload->addFilter('Rename', array(
                'target' => $fileFullPath,
                'overwrite' => true
            ));

            $fileData = array(
                'operation' => 'receive_uploaded_file',
                'src_name'  => $file,
                'dst_name'  => $fileFullPath,
                'uploader'  => $upload,
                'option'    => $this,
            );

            if(Mage::app()->getRequest()->getParam('configurable_grid_table') == 'Yes') {
                if(Mage::registry('configurable_file_last')) {
                    $fileData['configurable_file_last'] = 1;
                    Mage::unregister('configurable_file_last');
                }
            }

            $this->getProduct()->getTypeInstance(true)->addFileQueue($fileData);
            $_width = 0;
            $_height = 0;
            if (is_readable($fileInfo['tmp_name'])) {
                $_imageSize = getimagesize($fileInfo['tmp_name']);
                if ($_imageSize) {
                    $_width = $_imageSize[0];
                    $_height = $_imageSize[1];
                }
            }

            $this->setUserValue(array(
                'type'          => $fileInfo['type'],
                'title'         => $fileInfo['name'],
                'quote_path'    => $this->getQuoteTargetDir(true) . $filePath,
                'order_path'    => $this->getOrderTargetDir(true) . $filePath,
                'fullpath'      => $fileFullPath,
                'size'          => $fileInfo['size'],
                'width'         => $_width,
                'height'        => $_height,
                'secret_key'    => substr($fileHash, 0, 20),
            ));

        } elseif ($upload->getErrors()) {
            $errors = $this->_getValidatorErrors($upload->getErrors(), $fileInfo);

            if (count($errors) > 0) {
                $this->setIsValid(false);
                Mage::throwException( implode("\n", $errors) );
            }
        } else {
            $this->setIsValid(false);
            Mage::throwException(Mage::helper('catalog')->__('Please specify the product required option(s)'));
        }
        return $this;
    }
	
	public function prepareForCart() {
		if(!Mage::helper('configurablegridview')->getSetting('is_enabled')) {
			return parent::prepareForCart();
		}
		
        $option = $this->getOption();
        $optionId = $option->getId();
        $buyRequest = $this->getRequest();

        // Prepare value and fill buyRequest with option
        $requestOptions = $buyRequest->getOptions();
        if ($this->getIsValid() && $this->getUserValue() !== null) {
            $value = $this->getUserValue();

            // Save option in request, because we have no $_FILES['options']
            $requestOptions[$this->getOption()->getId()] = $value;
            $result = serialize($value);
            try {
                Mage::helper('core/unserializeArray')->unserialize($result);
            } catch (Exception $e) {
                Mage::throwException(Mage::helper('catalog')->__("File options format is not valid."));
            }
        } else {
            /*
             * Clear option info from request, so it won't be stored in our db upon
             * unsuccessful validation. Otherwise some bad file data can happen in buyRequest
             * and be used later in reorders and reconfigurations.
             */
            if (is_array($requestOptions)) {
                unset($requestOptions[$this->getOption()->getId()]);
            }
            $result = null;
        }
        $buyRequest->setOptions($requestOptions);

        if(!Mage::app()->getRequest()->getParam('configurable_grid_table') == 'Yes') {
            // Clear action key from buy request - we won't need it anymore
            $optionActionKey = 'options_' . $optionId . '_file_action';
            $buyRequest->unsetData($optionActionKey);
        }
        return $result;
    }
}