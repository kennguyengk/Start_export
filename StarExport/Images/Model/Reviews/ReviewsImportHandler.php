<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace StarExport\Images\Model\Reviews;

/**
 * Tax Rate CSV Import Handler
 */
class ReviewsImportHandler
{
    /**
     * Collection of publicly available stores
     *
     * @var \Magento\Store\Model\ResourceModel\Store\Collection
     */
    protected $_publicStores;

    /**
     * Region collection prototype
     *
     * The instance is used to retrieve regions based on country code
     *
     * @var \Magento\Directory\Model\ResourceModel\Region\Collection
     */
    protected $_regionCollection;

    /**
     * Country factory
     *
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $_countryFactory;

    /**
     * Tax rate factory
     *
     * @var \Magento\Tax\Model\Calculation\RateFactory
     */
    protected $_taxRateFactory;

    /**
     * CSV Processor
     *
     * @var \Magento\Framework\File\Csv
     */
    protected $csvProcessor;
    protected $reviewCollection;

    /**
     * @param \Magento\Store\Model\ResourceModel\Store\Collection $storeCollection
     * @param \Magento\Directory\Model\ResourceModel\Region\Collection $regionCollection
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Tax\Model\Calculation\RateFactory $taxRateFactory
     * @param \Magento\Framework\File\Csv $csvProcessor
     */
    public function __construct(
        \Magento\Store\Model\ResourceModel\Store\Collection $storeCollection,
        \Magento\Directory\Model\ResourceModel\Region\Collection $regionCollection,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Tax\Model\Calculation\RateFactory $taxRateFactory,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Review\Model\ResourceModel\Review\Collection $reviewCollection
    ) {
				//die('sadfsdfgsdg');
        // prevent admin store from loading
        $this->_publicStores = $storeCollection->setLoadDefault(false);
        $this->_regionCollection = $regionCollection;
        $this->_countryFactory = $countryFactory;
        $this->_taxRateFactory = $taxRateFactory;
        $this->csvProcessor = $csvProcessor;
        $this->reviewCollection = $reviewCollection;
    }

    /**
     * Retrieve a list of fields required for CSV file (order is important!)
     *
     * @return array
     */
    public function getRequiredCsvFields()
    {
        // indexes are specified for clarity, they are used during import
        return [
            0 => __('created_at'),
            1 => __('product_id'),
            2 => __('status_id'),
            3 => __('Title'),
            4 => __('Detail'),
            5 => __('nickname'),
            6 => __('customer_id'),
            7 => __('option_id'),
            8 =>__('stores')
        ];
    }

    /**
     * Import Tax Rates from CSV file
     *
     * @param array $file file info retrieved from $_FILES array
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function importFromCsvFile($file)
    {
        if (!isset($file['tmp_name'])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file upload attempt.'));
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $ratesRawData = $this->csvProcessor->getData($file['tmp_name']);
        // first row of file represents headers
        $fileFields = $ratesRawData[0];
        $validFields = $this->_filterFileFields($fileFields);
        $invalidFields = array_diff_key($fileFields, $validFields);
        $ratesData = $this->_filterReviewData($ratesRawData, $invalidFields, $validFields);
        // store cache array is used to quickly retrieve store ID when handling locale-specific tax rate titles
        $storesCache = $this->_composeStoreCache($validFields);
        $regionsCache = [];
        
        $db_in = \Magento\Framework\App\ObjectManager::getInstance()
        ->get('Magento\Framework\App\ResourceConnection');
        $db_connection= $db_in->getConnection();
        
        foreach ($ratesData as $rowIndex => $dataRow) {
            // skip headers
            if ($rowIndex == 0) {
                continue;
            }
            
            $regionsCache = $this->_importRate($dataRow, $regionsCache, $storesCache, $db_connection);
        }
    }

    /**
     * Filter file fields (i.e. unset invalid fields)
     *
     * @param array $fileFields
     * @return string[] filtered fields
     */
    protected function _filterFileFields(array $fileFields)
    {
        $filteredFields = $this->getRequiredCsvFields();
        $requiredFieldsNum = count($this->getRequiredCsvFields());
        $fileFieldsNum = count($fileFields);

        // process title-related fields that are located right after required fields with store code as field name)
        for ($index = $requiredFieldsNum; $index < $fileFieldsNum; $index++) {
            $titleFieldName = $fileFields[$index];
            if ($this->_isStoreCodeValid($titleFieldName)) {
                // if store is still valid, append this field to valid file fields
                $filteredFields[$index] = $titleFieldName;
            }
        }

        return $filteredFields;
    }

    /**
     * Filter rates data (i.e. unset all invalid fields and check consistency)
     *
     * @param array $rateRawData
     * @param array $invalidFields assoc array of invalid file fields
     * @param array $validFields assoc array of valid file fields
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _filterReviewData(array $rateRawData, array $invalidFields, array $validFields)
    {
        $validFieldsNum = count($validFields);
        foreach ($rateRawData as $rowIndex => $dataRow) {
            // skip empty rows
            if (count($dataRow) <= 1) {
                unset($rateRawData[$rowIndex]);
                continue;
            }
            // unset invalid fields from data row
            foreach ($dataRow as $fieldIndex => $fieldValue) {
                if (isset($invalidFields[$fieldIndex])) {
                    unset($rateRawData[$rowIndex][$fieldIndex]);
                }
            }
            // check if number of fields in row match with number of valid fields
          /*  if (count($rateRawData[$rowIndex]) != $validFieldsNum) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file format.'));
            } */
        }
        return $rateRawData;
    }

    /**
     * Compose stores cache
     *
     * This cache is used to quickly retrieve store ID when handling locale-specific tax rate titles
     *
     * @param string[] $validFields list of valid CSV file fields
     * @return array
     */
    protected function _composeStoreCache($validFields)
    {
        $storesCache = [];
        $requiredFieldsNum = count($this->getRequiredCsvFields());
        $validFieldsNum = count($validFields);
        // title related fields located right after required fields
        for ($index = $requiredFieldsNum; $index < $validFieldsNum; $index++) {
            foreach ($this->_publicStores as $store) {
                $storeCode = $validFields[$index];
                if ($storeCode === $store->getCode()) {
                    $storesCache[$index] = $store->getId();
                }
            }
        }
        return $storesCache;
    }

    /**
     * Check if public store with specified code still exists
     *
     * @param string $storeCode
     * @return boolean
     */
    protected function _isStoreCodeValid($storeCode)
    {
        $isStoreCodeValid = false;
        foreach ($this->_publicStores as $store) {
            if ($storeCode === $store->getCode()) {
                $isStoreCodeValid = true;
                break;
            }
        }
        return $isStoreCodeValid;
    }
    protected function _importRate(array $reviewData, array $regionsCache, array $storesCache, $db_connection)
    {
           $product_id = $reviewData[1];
			if($reviewData[6]== 0)
			{
				$customerid = NULL; 
			}   
			else
			{
				$customerid = $reviewData[6];                 
			}
			
			if(empty($reviewData[8]))
				$stores = 1;
			else
			{
				$stores = explode(',',$reviewData[8]);
			}
			
			if($reviewData[6] != ""){    
				$fetch_cus_query = "Select entity_id FROM customer_entity WHERE entity_id = $reviewData[6]";
				$cus_result = $db_connection->fetchrow($fetch_cus_query);
				if(!$cus_result){
					$customerid = NULL;
				}
			}
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$_review =   $objectManager->create('Magento\Review\Model\Review')
			->setCreatedAt($reviewData[0])
			->setEntityPkValue($product_id)
			->setEntityId(1)
			->setStatusId($reviewData[2])
			->setTitle($reviewData[3])
			->setDetail($reviewData[4])
			->setStoreId(1)
			->setStores($stores)
			->setCustomerId($customerid)
			->setNickname($reviewData[5])
			->save();
			if($reviewData[7])
			{
				$arr_data = explode("@",$reviewData[7]);
				if(!empty($arr_data)) {						
					foreach($arr_data as $each_data) {
						$arr_rating = explode(":",$each_data);					  
						if($arr_rating[1] != 0) {
						 $objectManager->create('Magento\Review\Model\Rating')
							->setRatingId($arr_rating[0])
							->setReviewId($_review->getId())
							->setCustomerId($customerid)
							->addOptionVote($arr_rating[1], $product_id);
						}
					}                                        
				}     
				$_review->aggregate();
			}
			return $regionsCache;
    } 
   
}
