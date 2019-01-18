<?php
namespace StarExport\Images\Helper;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Data
 *
 * @author mannu
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    //put your code here
    protected $scopeConfig;

    /**
     * helper
     */
    public function __construct(
    \Magento\Framework\App\Helper\Context $context, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
    }

    public function getStoreConfig($path) {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

}
