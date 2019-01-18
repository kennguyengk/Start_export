<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace StarExport\Images\Controller\Adminhtml\Reviews;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportReviews extends \StarExport\Images\Controller\Adminhtml\Reviews
{
	
	/**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        parent::__construct($context,$fileFactory);
    }
    
    /**
     * Export action from import/export tax
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        /** start csv content and set template */
        $headers = new \Magento\Framework\DataObject(
            [
                'created_at' => __('created_at'),
                'entity_pk_value' => __('product_id'),
                'status_id' => __('status_id'),
                'title' => __('Title'),
                'detail' => __('Detail'),
                'nickname' => __('nickname'),
                'customer_id' => __('customer_id'),
                'option_id' => __('option_id'),
                'stores' => __('stores'),
            ]
        );
        $template = '"{{created_at}}","{{entity_pk_value}}","{{status_id}}","{{title}}","{{detail}}"' .
            ',"{{nickname}}","{{customer_id}}","{{option_id}}","{{stores}}"';
        $content = $headers->toString($template);

        $storeTaxTitleTemplate = [];
        $taxCalculationRateTitleDict = [];

        foreach ($this->_objectManager->create(
            'Magento\Store\Model\Store'
        )->getCollection()->setLoadDefault(
            false
        ) as $store) {
            $storeTitle = 'title_' . $store->getId();
            $content .= ',"' . $store->getCode() . '"';
            $template .= ',"{{' . $storeTitle . '}}"';
            $storeTaxTitleTemplate[$storeTitle] = null;
        }
        unset($store);

        $content .= "\n";

        foreach ($this->_objectManager->create(
            'Magento\Tax\Model\Calculation\Rate\Title'
        )->getCollection() as $title) {
            $rateId = $title->getTaxCalculationRateId();

            if (!array_key_exists($rateId, $taxCalculationRateTitleDict)) {
                $taxCalculationRateTitleDict[$rateId] = $storeTaxTitleTemplate;
            }

            $taxCalculationRateTitleDict[$rateId]['title_' . $title->getStoreId()] = $title->getValue();
        } 
        unset($title);

        $collection = $this->_objectManager->create(
            'Magento\Review\Model\ResourceModel\Review\Collection'
        );

        while ($rate = $collection->fetchItem()) {
			//var_dump($rate->getStores());die('inn');
            if ($rate->getTaxRegionId() == 0) {
                $rate->setRegionName('*');
            }
             if ($rate->getEntityPkValue() == 0) {
                continue;
            }
            if($rate->getStores())
            {
				$stores = $rate->getStores();
				foreach($stores as $key=>$store)
			{
				if($key == 0)
				{
					$allStore = $store;
					$rate->setStores($allStore);
				}
				else
				{
					$allStore = $allStore.','.$store;
					$rate->setStores($allStore);
				}			
			}
			}
            if($rate->getEntityPkValue()){
				$ratingCollection = $this->_objectManager->create('Magento\Review\Model\Rating\Option\Vote')
					->getResourceCollection()
					->setReviewFilter($rate->getId());
					$rating_val = "";
					$option = "";
					$option_value = '';
					foreach($ratingCollection as $rating)
					{					 
						$option =  $rating->getOptionId();                        
						$rating_val = $rating->getRatingId(); 
						
						if(!empty($option_value) && $option_value != '') 
							$option_value = $option_value."@".$rating_val.":".$option; 
						 else
							$option_value = $rating_val.":".$option;
							
					}
					$rate->setOptionId($option_value);
			}
            //$rate->setOptionId("1:3@2:3@3:3@4:3");
            /* if (array_key_exists($rate->getId(), $taxCalculationRateTitleDict)) {
                $rate->addData($taxCalculationRateTitleDict[$rate->getId()]);
            } else {
                $rate->addData($storeTaxTitleTemplate);
            } */
			//var_dump($template);die;
            $content .= $rate->toString($template) . "\n";
        }
        return $this->fileFactory->create('reviews.csv', $content, DirectoryList::VAR_DIR);
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
