<?php
namespace Recapture\Connector\Observer;

class QuoteConversion implements \Magento\Framework\Event\ObserverInterface {

    protected $helper;
    protected $transport;
    protected $logger;

    public function __construct(
        \Recapture\Connector\Helper\Data $helper,
        \Recapture\Connector\Helper\Transport $transport,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->helper      = $helper;
        $this->transport   = $transport;
        $this->logger      = $logger;
    }


    public function execute(\Magento\Framework\Event\Observer $observer){

        if (!$this->helper->isEnabled()) return $this;

        try {

            $order = $observer->getEvent()->getOrder();

            $transportData = array(
                'external_id'  => $order->getQuoteId()
            );

            $this->transport->dispatch('conversion', $transportData);

        } catch (\Exception $e){

            $this->logger->critical($e);

        }

        return $this;

    }

}
