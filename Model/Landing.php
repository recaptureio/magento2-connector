<?php

namespace Recapture\Connector\Model;

class Landing implements \Magento\Framework\Option\ArrayInterface {

    protected $helper;

    public function __construct(
        \Recapture\Connector\Helper\Data $helper
    ) {
        $this->helper      = $helper;
    }

    const REDIRECT_HOME     = 'home';
    const REDIRECT_CART     = 'cart';
    const REDIRECT_CHECKOUT = 'checkout';

    public function toOptionArray(){

        return array(
            array('value' => self::REDIRECT_HOME, 'label' => 'Home Page'),
            array('value' => self::REDIRECT_CART, 'label' => 'Cart Page'),
            array('value' => self::REDIRECT_CHECKOUT, 'label' => 'Checkout Page'),
        );
    }

}
