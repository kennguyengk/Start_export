<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace StarExport\Images\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;

/**
 * Review resource model
 *
 * @api
 * @since 100.0.2
 */
class Review extends \Magento\Review\Model\ResourceModel\Review
{
       
    protected function _beforeSave(AbstractModel $object)
    {
        if (!$object->getId()) {
            //$object->setCreatedAt($this->_date->gmtDate());
        }
        if ($object->hasData('stores') && is_array($object->getStores())) {
            $stores = $object->getStores();
            $stores[] = 0;
            $object->setStores($stores);
        } elseif ($object->hasData('stores')) {
            $object->setStores([$object->getStores(), 0]);
        }
        return $this;
    }

    
}
