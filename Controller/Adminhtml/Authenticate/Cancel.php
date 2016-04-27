<?php

namespace Recapture\Connector\Controller\Adminhtml\Authenticate;

class Cancel extends \Magento\Backend\App\Action {

    protected $helper;
    protected $backendHelper;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Recapture\Connector\Helper\Data $helper,
        \Magento\Backend\Helper\Data $backendHelper
    ) {
        $this->helper        = $helper;
        $this->backendHelper = $backendHelper;

        parent::__construct($context);
    }

    public function execute(){

        $resultRedirect = $this->resultRedirectFactory->create();

        $this->messageManager->addError('Authentication has been cancelled.');

        $scope = $this->helper->getScopeForUrl();
        $scope['section'] = 'recapture';

        $resultRedirect->setPath('adminhtml/system_config/edit', $scope);
        return $resultRedirect;

    }

}
