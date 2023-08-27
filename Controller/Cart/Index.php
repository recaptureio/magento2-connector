<?php

namespace Recapture\Connector\Controller\Cart;

use Recapture\Connector\Model\Landing;

class Index extends \Magento\Framework\App\Action\Action {

    protected $helper;
    protected $invalidator;
    protected $transport;
    protected $logger;
    protected $messageManager;
    protected $urlInterface;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Recapture\Connector\Helper\Data $helper,
        \Recapture\Connector\Helper\Invalidator $invalidator,
        \Recapture\Connector\Helper\Transport $transport,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\UrlInterface $urlInterface
    ) {
        $this->helper         = $helper;
        $this->invalidator    = $invalidator;
        $this->transport      = $transport;
        $this->logger         = $logger;
        $this->urlInterface   = $urlInterface;

        parent::__construct($context);
    }

    public function execute(){

        if (!$this->helper->isEnabled() || !$this->helper->getApiKey()){
            return $this->_redirect('/');
        }

        $hash = $this->getRequest()->getParam('hash');

        try {

            $cartId = $this->_translateCartHash($hash);

        } catch (\Exception $e){

            $this->logger->critical($e);

        }

        if (!$cartId){

            $this->messageManager->addError(__('There was an error retrieving your cart.'));

            return $this->_redirect('/');

        }

        try {

            $result = $this->helper->associateCartToMe($cartId);

        } catch (\Exception $e){

            $this->logger->critical($e);

        }

        if (!$result){

            $this->messageManager->addError('There was an error retrieving your cart.');
            return $this->_redirect('/');

        } else {

            $this->invalidator->invalidate('customer')->invalidate('cart');
            $redirectSection = $this->helper->getReturnLanding();

            switch ($redirectSection){

                case Landing::REDIRECT_HOME:

                    return $this->_redirect('/');

                case Landing::REDIRECT_CHECKOUT:

                    return $this->_redirect($this->urlInterface->getUrl('checkout', ['_secure' => true]));

                case Landing::REDIRECT_CART:
                default:

                    return $this->_redirect($this->urlInterface->getUrl('checkout/cart', ['_secure' => true]));

            }

            return $this->_redirect('/');

        }
    }

    private function _translateCartHash($hash = null){

        if (empty($hash)) return false;

        $result = $this->transport->dispatch('cart/retrieve', array(
            'hash' => $hash
        ));

        $body = @json_decode($result->getBody());

        if ($body->status == 'success'){

            return $body->data->cart_id;

        } else return false;

    }

}
