<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace StarExport\Images\Block\Adminhtml;

class ImportExportReviews extends \Magento\Backend\Block\Widget
{
    /**
     * @var string
     */
    protected $_template = 'reviews.phtml';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, array $data = [])
    {
        parent::__construct($context, $data);
        $this->setUseContainer(true);
    }
}
