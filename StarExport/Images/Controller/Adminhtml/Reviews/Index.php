<?php
namespace StarExport\Images\Controller\Adminhtml\Reviews;
/**
 * Description of Index
 *
 * @author mannu
 */
use Magento\Framework\Controller\ResultFactory;
class Index extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $resultPage->setActiveMenu('Magento_TaxImportExport::system_convert_tax');
        $resultPage->addContent(
            $resultPage->getLayout()->createBlock('StarExport\Images\Block\Adminhtml\Reviews')
        );
        $resultPage->addContent(
            $resultPage->getLayout()->createBlock('StarExport\Images\Block\Adminhtml\ImportExportReviews')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Reviews'));
        $resultPage->getConfig()->getTitle()->prepend(__('Import and Export Reviews'));
        return $resultPage;
    }
}
