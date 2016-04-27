<?php
namespace Recapture\Connector\Block\Adminhtml\System\Config;

class Authenticate extends \Magento\Config\Block\System\Config\Form\Field {

    protected $helper;
    protected $backendHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Recapture\Connector\Helper\Data $helper,
        \Magento\Backend\Helper\Data $backendHelper
    ) {
        $this->helper        = $helper;
        $this->backendHelper = $backendHelper;
        parent::__construct($context, []);
    }

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element){

        $clickUrl = $this->backendHelper->getUrl("recapture_adminhtml/authenticate", $this->helper->getScopeForUrl());

        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'label' => __('Authenticate Account'),
                'onclick' => 'setLocation(\''. $clickUrl . '\' )',
                'class'     => '',
            ]
        );

        return $button->toHtml();
    }
}
