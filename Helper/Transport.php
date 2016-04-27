<?php

namespace Recapture\Connector\Helper;

use Zend\Http\Client;

class Transport extends \Magento\Framework\App\Helper\AbstractHelper {

    protected $helper;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Recapture\Connector\Helper\Data $helper
    ) {
        $this->helper = $helper;
        parent::__construct($context);
    }

    public function dispatch($route = '', $data = array()){

        $response = null;

        try {
          if (!$this->helper->isEnabled()) return false;
          if (empty($route)) return false;

          $client  = new Client($this->helper->getHomeUrl('beacon/' . $route), array(
              'timeout' => 1
          ));

          $data['customer'] = $this->helper->getCustomerHash();

          $client->setMethod('POST');
          $client->setParameterPost($data);

          $client->setHeaders(array('Api-Key' => $this->helper->getApiKey()));
          $response = $client->send();

        } catch (\Exception $e){

          $this->_logger->error($e);

        }

        return $response;

    }
}
