<?php

namespace Recapture\Connector\Helper;

class Invalidator extends \Magento\Framework\App\Helper\AbstractHelper {

    protected $session;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $session
    ) {
        $this->session    = $session;
        parent::__construct($context);
    }

    public function invalidate($key){

        $invalidations = $this->getInvalidations();

        if (!$invalidations){
          $invalidations = array();
        }

        $invalidations[] = $key;

        $this->setInvalidations($invalidations);

        return $this;

    }

    public function getInvalidations(){

      $invalidations = is_array($this->session->getRecaptureInvalidation()) ? $this->session->getRecaptureInvalidation() : array();
      $this->session->setRecaptureInvalidation(array());

      return $invalidations;

    }

    public function setInvalidations($invalidations){

      return $this->session->setRecaptureInvalidation($invalidations);

    }

}
