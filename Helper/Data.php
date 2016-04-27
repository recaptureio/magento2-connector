<?php

namespace Recapture\Connector\Helper;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Config\Model\Config;
use Magento\Store\Model\Store;
use Magento\Store\Model\Website;
use Magento\Quote\Model\QuoteFactory;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    protected $scopeConfigWriter;
    protected $scopeConfig;
    protected $checkoutSession;
    protected $storeModel;
    protected $websiteModel;
    protected $moduleList;
    protected $quoteFactory;
    protected $visitorSession;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\Storage\Writer $scopeConfigWriter,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Session\SessionManagerInterface $session,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        Store $storeModel,
        Website $websiteModel,
        QuoteFactory $quoteFactory
    ) {
        $this->scopeConfig       = $scopeConfig;
        $this->scopeConfigWriter = $scopeConfigWriter;
        $this->customerSession   = $customerSession;
        $this->checkoutSession   = $checkoutSession;
        $this->visitorSession    = $session;
        $this->storeModel        = $storeModel;
        $this->websiteModel      = $websiteModel;
        $this->quoteFactory      = $quoteFactory;
        $this->moduleList        = $moduleList;
        parent::__construct($context);
    }

    public function getVersion(){

        return $this->moduleList->getOne('Recapture_Connector')['setup_version'];

    }

    public function getConfig($configKey, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null){

        return $this->scopeConfig->getValue($configKey, $scope, $scopeCode);

    }

    public function getStoreConfig($configKey){
        return $this->storeModel->getConfig($configKey);
    }

    public function saveConfig($path, $value, $scope, $scopeId){

        return $this->scopeConfigWriter->save($path, $value, $scope, $scopeId);

    }

    public function isEnabled(){

        return $this->getStoreConfig('recapture/configuration/enabled');

    }

    public function shouldCaptureSubscriber(){

        return $this->getStoreConfig('recapture/abandoned_carts/capture_subscriber');

    }

    public function getHomeUrl($path){

        $baseUrl = $this->getStoreConfig('recapture/configuration/dev_base_url');
        if (!$baseUrl) $baseUrl = 'https://www.recapture.io/';

        return $baseUrl . $path;

    }

    public function getAnalyticsUrl(){

        $queueUrl = $this->getStoreConfig('recapture/configuration/dev_analytics_url');
        if (!$queueUrl) $queueUrl = '//cdn.recapture.io/sdk/v1/ra.min.js';

        return $queueUrl;

    }

    public function canTrackEmail(){

        return $this->getStoreConfig('recapture/abandoned_carts/track_email');

    }

    public function getReturnLanding(){

        return $this->getStoreConfig('recapture/abandoned_carts/return_landing');

    }

    public function getApiKey(){

        return $this->getStoreConfig('recapture/configuration/api_key');

    }

    public function getActiveWebsite(){

        $website = $this->_request->getParam('website');
        $website = !empty($website) ? $website : null;

        return $website;

    }

    public function getActiveStore(){

        $store = $this->_request->getParam('store');
        $store = !empty($store) ? $store : null;

        return $store;

    }


    public function getScopeStoreId(){

        $website = $this->getActiveWebsite();
        $store   = $this->getActiveStore();

        if (!$website && !$store) return '0';

        if ($store) return $this->storeModel->load($store)->getId();
        if ($website) return $this->websiteModel->load($website)->getDefaultGroup()->getDefaultStoreId();



    }

    public function getCurrentScope(){

        $website = $this->getActiveWebsite();
        $store   = $this->getActiveStore();

        if (!$website && !$store) return 'default';

        if ($store) return 'stores';
        if ($website) return 'websites';

    }

    public function getScopeForUrl(){

        $website = $this->getActiveWebsite();
        $store   = $this->getActiveStore();

        if (!$website && !$store) return array();

        if ($store) return array('website' => $website, 'store' => $store);
        if ($website) return array('website' => $website);

    }

    public function getCurrentScopeId(){

        $website = $this->getActiveWebsite();
        $store   = $this->getActiveStore();

        if (!$website && !$store) return 0;

        if ($store) return $this->storeModel->load($store)->getId();
        if ($website) return $this->websiteModel->load($website)->getId();

    }

    public function associateCartToMe($cartId = null){

        if (empty($cartId)) return false;

        $quote = $this->quoteFactory->create()->load($cartId);

        //if quote has a customer id, log them in
        if ($quote->getCustomerId()){
            $this->customerSession->loginById($quote->getCustomerId());

        //otherwise just set the quote id
        } else {
            $this->checkoutSession->replaceQuote($quote);
        }

        //if this cart somehow was already converted, we're not going to be able to load it. as such, we can't associate it.
        if ($this->checkoutSession->getQuote()->getId() != $cartId) return false;

        return true;

    }

    public function getCustomerFirstname(\Magento\Quote\Model\Quote $quote){

        //we first check the quote model itself
        $customerFirstname = $quote->getCustomerFirstname();
        if (!empty($customerFirstname)) return $customerFirstname;

        //if not on the quote model, we check the billing address
        $billingAddress = $quote->getBillingAddress();
        if ($billingAddress){

            $customerFirstname = $billingAddress->getFirstname();
            if (!empty($customerFirstname)) return $customerFirstname;

        }

        //if not in the billing address, last resort we check the shipping address
        $shippingAddress = $quote->getShippingAddress();
        if ($shippingAddress){

            $customerFirstname = $shippingAddress->getFirstname();
            if (!empty($customerFirstname)) return $customerFirstname;

        }

        return null;

    }

    public function getCustomerLastname(\Magento\Quote\Model\Quote $quote){

        //we first check the quote model itself
        $customerLastname = $quote->getCustomerLastname();
        if (!empty($customerLastname)) return $customerLastname;

        //if not on the quote model, we check the billing address
        $billingAddress = $quote->getBillingAddress();
        if ($billingAddress){

            $customerLastname = $billingAddress->getLastname();
            if (!empty($customerLastname)) return $customerLastname;

        }

        //if not in the billing address, last resort we check the shipping address
        $shippingAddress = $quote->getShippingAddress();
        if ($shippingAddress){

            $customerLastname = $shippingAddress->getLastname();
            if (!empty($customerLastname)) return $customerLastname;

        }

        return null;

    }

    public function getCustomerEmail(\Magento\Quote\Model\Quote $quote){

        //we first check the quote model itself
        $customerEmail = $quote->getCustomerEmail();
        if (!empty($customerEmail)) return $customerEmail;

        //if not on the quote model, we check the billing address
        $billingAddress = $quote->getBillingAddress();
        if ($billingAddress){

            $customerEmail = $billingAddress->getEmail();
            if (!empty($customerEmail)) return $customerEmail;

        }

        //if not in the billing address, last resort we check the shipping address
        $shippingAddress = $quote->getShippingAddress();
        if ($shippingAddress){

            $customerEmail = $shippingAddress->getEmail();
            if (!empty($customerEmail)) return $customerEmail;

        }

        return null;

    }


    public function getCustomerHash(){

        return isset($_COOKIE['ra_customer_id']) ? $_COOKIE['ra_customer_id'] : null;

    }

}
