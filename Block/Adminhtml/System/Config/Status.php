<?php
namespace Recapture\Connector\Block\Adminhtml\System\Config;

class Status extends \Magento\Config\Block\System\Config\Form\Field {

    protected $helper;
    protected $backendHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Recapture\Connector\Helper\Data $helper
    ) {
        $this->helper = $helper;
        parent::__construct($context, []);
    }

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element) {

        $authenticated = $this->helper->getConfig('recapture/configuration/authenticated', $this->helper->getCurrentScope(),  $this->helper->getScopeStoreId());

        $text = '<span class="' . ($authenticated ? 'success' : 'error') . '">';
        $text .= $authenticated ? 'Authenticated!' : 'Not Authenticated';
        $text .= '</span>';

        return $text;

    }

}
