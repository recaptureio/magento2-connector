<?php

namespace Recapture\Connector\Controller\Adminhtml\Authenticate;

class Complete extends \Magento\Backend\App\Action {

    protected $helper;
    protected $backendHelper;
    protected $cache;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Recapture\Connector\Helper\Data $helper,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
    ) {
        $this->helper = $helper;
        $this->cache  = $cacheTypeList;

        parent::__construct($context);
    }

    public function execute(){

        $redirect = $this->resultRedirectFactory->create();

        $apiKey = $this->getRequest()->getParam('response_key');

        $helper = $this->helper;
        $scope = $helper->getCurrentScope();
        $scopeId = $helper->getCurrentScopeId();

        if ($apiKey){

            $this->helper->saveConfig('recapture/configuration/authenticated', true, $scope, $scopeId);
            $this->helper->saveConfig('recapture/configuration/api_key', $apiKey, $scope, $scopeId);
            $this->helper->saveConfig('recapture/configuration/enabled', true, $scope, $scopeId);

            $this->cache->cleanType('config');

            $this->messageManager->addSuccess('Your account has been authenticated successfully!');

        } else {

            $this->messageManager->addError('Unable to authenticate your account. Please ensure you are logged in to your Recapture account.');

        }

        $scope = $this->helper->getScopeForUrl();
        $scope['section'] = 'recapture';

        $redirect->setPath('adminhtml/system_config/edit', $scope);
        return $redirect;

    }

}
