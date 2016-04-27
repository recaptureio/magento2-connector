<?php
namespace Recapture\Connector\Observer;

class NewsletterSubscribe implements \Magento\Framework\Event\ObserverInterface {

    protected $helper;
    protected $transport;

    public function __construct(
        \Recapture\Connector\Helper\Data $helper,
        \Recapture\Connector\Helper\Transport $transport
    ) {
        $this->helper      = $helper;
        $this->transport   = $transport;
    }

    public function execute(\Magento\Framework\Event\Observer $observer){

        if (!$this->helper->isEnabled()) return $this;

        //if we can't identify this customer, we return out
        if (!$this->helper->getCustomerHash()) return $this;

        $subscriber = $observer->getEvent()->getSubscriber();

        $transportData = array(
            'email'  => $subscriber->getSubscriberEmail()
        );
        
        $this->transport->dispatch('email/subscribe', $transportData);

        return $this;


    }

}
