<?php
namespace Recapture\Connector\Observer;

class QuoteUpdate implements \Magento\Framework\Event\ObserverInterface {

    protected $helper;
    protected $transport;
    protected $storeManager;
    protected $mediaConfig;
    protected $productResource;
    protected $optionsHelper;
    protected $logger;

    public function __construct(
        \Recapture\Connector\Helper\Data $helper,
        \Magento\Framework\Registry $registry,
        \Recapture\Connector\Helper\Transport $transport,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Catalog\Helper\Product\Configuration $optionsHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->helper          = $helper;
        $this->transport       = $transport;
        $this->registry        = $registry;
        $this->storeManager    = $storeManager;
        $this->mediaConfig     = $mediaConfig;
        $this->productResource = $productResource;
        $this->optionsHelper   = $optionsHelper;
        $this->logger          = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer){

        $event = $observer->getEvent();
        if ($event->getCart()){
            $quote = $event->getCart()->getQuote();
        } else {
            $quote = $event->getQuote();
        }

        try {

            return $this->_updateQuote($quote);

        } catch (\Exception $e){

            $this->logger->critical($e);

        }

        return $this;

    }

    protected function _updateQuote(\Magento\Quote\Model\Quote $quote){

        if (!$this->helper->isEnabled()) return $this;

        if (!$quote->getId()) return;

        //sales_quote_save_before gets called like 5 times on some page loads, we don't want to do 5 updates per page load
        if ($this->registry->registry('recapture_has_posted')) return;

        $this->registry->register('recapture_has_posted', true, true);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $priceHelper   = $objectManager->create('Magento\Framework\Pricing\Helper\Data');
        $mediaConfig   = $this->mediaConfig;
        $store         = $this->storeManager->getStore();

        $transportData = array(
            'first_name'          => $this->helper->getCustomerFirstname($quote),
            'last_name'           => $this->helper->getCustomerLastname($quote),
            'email'               => $this->helper->getCustomerEmail($quote),
            'external_id'         => $quote->getId(),
            'grand_total'         => $quote->getBaseGrandTotal(),
            'grand_total_display' => $priceHelper->currency($quote->getGrandTotal(), true, false),
            'products'            => array(),
            'totals'              => array()
        );

        $cartItems = $quote->getAllVisibleItems();

        foreach ($cartItems as $item){

            $productModel = $item->getProduct();

            $productImage = false;

            $image = $this->productResource->getAttributeRawValue($productModel->getId(), 'thumbnail', $store);

            if ($image && $image != 'no_selection') $productImage = $mediaConfig->getMediaUrl($image);

            //check configurable first
            if ($item->getProductType() == 'configurable'){

                if ($this->helper->getStoreConfig('checkout/cart/configurable_product_image') == 'itself'){

                    $child = $productModel->getIdBySku($item->getSku());

                    $image = $this->productResource->getAttributeRawValue($child, 'thumbnail', $store);
                    if ($image && $image != 'no_selection') $productImage = $mediaConfig->getMediaUrl($image);

                }
            }

            //then check grouped
            if ($this->helper->getStoreConfig('checkout/cart/grouped_product_image') == 'parent'){

                $options = $productModel->getTypeInstance(true)->getOrderOptions($productModel);

                if (isset($options['super_product_config']) && $options['super_product_config']['product_type'] == 'grouped'){

                    $parent = $options['super_product_config']['product_id'];
                    $image = $this->productResource->getAttributeRawValue($parent, 'thumbnail', $store);

                    if ($image && $image != 'no_selection') $productImage = $mediaConfig->getMediaUrl($image);

                }
            }

            //if after all that, we still don't have a product image, we get the placeholder image
            if (!$productImage) {

                $productImage = $mediaConfig->getMediaUrl('placeholder/' . $this->helper->getStoreConfig("catalog/placeholder/image_placeholder"));

            }

            $visibleOptions = $this->optionsHelper->getOptions($item);

            $product = array(
                'name'          => $item->getName(),
                'sku'           => $item->getSku(),
                'price'         => $item->getBasePrice(),
                'price_display' => $priceHelper->currency($item->getPrice(), true, false),
                'qty'           => $item->getQty(),
                'image'         => $productImage,
                'options'       => $visibleOptions
            );

            $transportData['products'][] = $product;

        }

        $totals = $quote->getTotals();

        foreach ($totals as $total){

            //we pass grand total on the top level
            if ($total->getCode() == 'grand_total') continue;

            $total = array(
                'name'   => (string)$total->getTitle(),
                'amount' => (string)$total->getValue()
            );

            $transportData['totals'][] = $total;

        }

        $this->transport->dispatch('cart', $transportData);

        return $this;

    }

}
