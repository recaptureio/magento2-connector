<?php

namespace Recapture\Connector\Block;

class Client extends \Magento\Framework\View\Element\Template {

    protected $helper;
    protected $registry;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Recapture\Connector\Helper\Data $helper,
        \Magento\Framework\Registry $registry
    ) {
        $this->helper = $helper;
        $this->registry = $registry;
        parent::__construct($context, []);
    }

    public function shouldTrack(){

        if (!$this->helper->isEnabled()) return false;

        $apiKey = $this->getApiKey();
        if (empty($apiKey)) return false;

        return true;

    }

    public function shouldTrackEmail(){

        if (!$this->shouldTrack()) return false;
        if (!$this->helper->canTrackEmail()) return false;

        return true;

    }

    public function getApiKey(){

        return $this->helper->getApiKey();

    }

    public function getCurrentProduct(){

        return $this->registry->registry('current_product');

    }

    public function getAnalyticsUrl(){

        $queueUrl = $this->helper->getAnalyticsUrl();

        //append a timestamp that changes every 10 minutes
        $queueUrl .= '?v=' . round(time() / (60 * 10));

        return $queueUrl;

    }

}
