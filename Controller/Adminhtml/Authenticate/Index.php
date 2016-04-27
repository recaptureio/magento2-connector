<?php

namespace Recapture\Connector\Controller\Adminhtml\Authenticate;

class Index extends \Magento\Backend\App\Action {

    protected $helper;
    protected $backendHelper;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Recapture\Connector\Helper\Data $helper
    ) {
        $this->helper        = $helper;

        parent::__construct($context);
    }

    public function execute(){

        $redirect = $this->resultRedirectFactory->create();

        if ($this->helper->getCurrentScope() == 'default'){

            $this->messageManager->addError('You cannot authenticate the Default Config scope. Please change the Store View on the top left to a specific website before authenticating.');

            $redirect->setPath('adminhtml/system_config/edit', array('section' => 'recapture'));
            return $redirect;

        }

        $scope = $this->helper->getScopeForUrl();

        $returnCancel = $this->_backendUrl->getUrl('recapture_adminhtml/authenticate/cancel', $scope);
        $scope['response_key'] = 'API_KEY';

        $returnConfirm = $this->_backendUrl->getUrl('recapture_adminhtml/authenticate/complete', $scope);
        $baseUrl  = $this->_url->getRouteUrl(null, array('_direct' => 'recapture/'));

        $query = http_build_query(array(
            'return'        => $returnConfirm,
            'return_cancel' => $returnCancel,
            'base'          => $baseUrl
        ));

        $authenticateUrl = $this->helper->getHomeUrl('account/auth?' . $query);

        $redirect->setUrl($authenticateUrl);
        return $redirect;

    }

}
