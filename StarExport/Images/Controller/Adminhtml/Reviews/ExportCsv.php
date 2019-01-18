<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace StarExport\Images\Controller\Adminhtml\Reviews;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;

class ExportCsv extends \StarExport\Images\Controller\Adminhtml\Reviews
{
    /**
     * Export rates grid to CSV format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        $content = $resultLayout->getLayout()->getChildBlock('adminhtml.tax.rate.grid', 'grid.export');

        return $this->fileFactory->create(
            'rates.csv',
            $content->getCsvFile(),
            DirectoryList::VAR_DIR
        );
    }
}
