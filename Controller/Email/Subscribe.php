<?php

namespace Recapture\Connector\Controller\Email;

use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Newsletter\Model\Subscriber;

class Subscribe extends \Magento\Framework\App\Action\Action {

    protected $helper;
    protected $transport;
    protected $subscriberFactory;
    protected $resultJsonFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Recapture\Connector\Helper\Data $helper,
        \Recapture\Connector\Helper\Transport $transport,
        SubscriberFactory $subscriberFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->helper    = $helper;
        $this->transport = $transport;
        $this->subscriberFactory = $subscriberFactory;
        $this->resultJsonFactory = $resultJsonFactory;

        parent::__construct($context);
    }


    public function execute(){

        $emailHashes = $this->getRequest()->getParam('hashes');

        $emails = $this->_translateEmailHashes($emailHashes);

        $subscriberModel = $this->subscriberFactory->create();
        $subscriberModel->setImportMode(true);

        if ($emails) foreach ($emails as $emailAddress){

            $subscriberModel->subscribe($emailAddress);

            $subscriber = $subscriberModel->loadByEmail($emailAddress);
            $subscriber->setStatus(Subscriber::STATUS_SUBSCRIBED);
            $subscriber->save();

        }

        $result = $this->resultJsonFactory->create();
        $result->setData(array('status' => 'success'));

        return $result;

    }

    protected function _translateEmailHashes($hashes = array()){

        if (empty($hashes)) return false;

        $result = $this->transport->dispatch('email/retrieve', array(
            'hashes' => $hashes
        ));

        $body = @json_decode($result->getBody());

        if ($body->status == 'success'){

            return $body->data->emails;

        } else return false;

    }

}
