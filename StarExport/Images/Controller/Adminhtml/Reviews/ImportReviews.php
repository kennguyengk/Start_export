<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace StarExport\Images\Controller\Adminhtml\Reviews;

use Magento\Framework\Controller\ResultFactory;

class ImportReviews extends \StarExport\Images\Controller\Adminhtml\Reviews
{
    /**
     * import action from import/export tax
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        if ($this->getRequest()->isPost() && !empty($_FILES['import_rates_file']['tmp_name'])) {
            try {
                /** @var $importHandler \Magento\TaxImportExport\Model\Rate\CsvImportHandler */
                $importHandler = $this->_objectManager->create('StarExport\Images\Model\Reviews\ReviewsImportHandler');
                $importHandler->importFromCsvFile($this->getRequest()->getFiles('import_rates_file'));

                $this->messageManager->addSuccess(__('The Reviews has been imported.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        } else {
            $this->messageManager->addError(__('Invalid file upload attempt'));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRedirectUrl());
        return $resultRedirect;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            'Magento_Tax::manage_tax'
        ) || $this->_authorization->isAllowed(
            'Magento_TaxImportExport::import_export'
        );

    }
}
