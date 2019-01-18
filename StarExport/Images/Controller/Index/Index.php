<?php
namespace StarExport\Images\Controller\Index;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Index
 *
 * @author mannu
 */
class Index extends \Magento\Framework\App\Action\Action {

    /** @var \Magento\Framework\View\Result\PageFactory  */
    protected $resultPageFactory;

    public function __construct(
    \Magento\Framework\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Load the page defined in view/frontend/layout/code_index_index.xml
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute() {
		//$object_manager = Magento\Core\Model\ObjectManager::getInstance();
       /* $_review =   $object_manager->create('Magento\Review\Model\ResourceModel\Review')
        //var_dump($review-getData());die;
			->setCreatedAt(date('Y-m-d H:i:s'))
			->setEntityPkValue(1)
			->setEntityId(1)
			->setStatusId(1)
			->setTitle("test")
			->setDetail("test test")
			->setStoreId(1)
			->setStores(1)
			->setCustomerId(NULL)
			->setNickname("gaurav")
			->save();*/
			
			$_review =   $this->_objectManager->create('Magento\Review\Model\Review')
			//die('innn');
			//var_dump($_review->getData());
			->setCreatedAt(date('Y-m-d H:i:s'))
			->setEntityPkValue(1)
			->setEntityId(1)
			->setStatusId(1)
			->setTitle("test")
			->setDetail("test test")
			->setStoreId(1)
			->setStores(1)
			->setCustomerId(NULL)
			->setNickname("gaurav")
			->save();
        return $this->resultPageFactory->create();
    }

}
