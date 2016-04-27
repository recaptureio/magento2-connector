<?php

namespace Recapture\Connector\Block;

class Invalidator extends \Magento\Customer\Block\CustomerData {

    protected $helper;
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Recapture\Connector\Helper\Invalidator $helper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->helper      = $helper;
        $this->scopeConfig = $scopeConfig;

        parent::__construct($context, []);
    }

    public function getInvalidations(){

        return $this->helper->getInvalidations();

    }

}
