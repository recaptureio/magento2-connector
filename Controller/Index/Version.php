<?php

namespace Recapture\Connector\Controller\Index;

class Version extends \Magento\Framework\App\Action\Action {

    protected $helper;
    protected $resultJsonFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Recapture\Connector\Helper\Data $helper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory

    ) {
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }


    public function execute(){

        $version  = $this->helper->getVersion();

        $result = $this->resultJsonFactory->create();
        $result->setData(array('version' => $version));

        return $result;

    }

}
