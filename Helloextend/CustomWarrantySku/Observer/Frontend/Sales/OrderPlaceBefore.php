<?php
/*
 * Custom Extend Module to change the SKU of the warranty product before the order is placed
 * the SKU was previously xtd-pp-pln , now we build a custom sku based on the API response.
 * in this example, 	xtd-pp-pln-<TERM_OF_WARRANTY>
 *
 * */

namespace Helloextend\CustomWarrantySku\Observer\Frontend\Sales;

class OrderPlaceBefore implements \Magento\Framework\Event\ObserverInterface
{
    protected \Magento\Catalog\Model\ProductRepository $_productRepository;
    private \Magento\Framework\Data\Form\FormKey $formKey;

    public function __construct(
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Data\Form\FormKey $formKey
    ){
        $this->_productRepository = $productRepository;
        $this->formKey = $formKey;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $order = $observer->getEvent()->getOrder();
        $items = $order->getItems();
        $option_value = null;
        foreach($items as $item){
            if ($item->getSku() == 'xtd-pp-pln'){
                $productOptions = $item->getProductOptions();
                if (isset($productOptions['additional_options'])){
                    foreach($productOptions['additional_options'] as $additional_option){
                        if ($additional_option['label'] == "Term"){
                            $option_value = $this->convertToShorthand($additional_option['value']);
                        }
                    }
                }
                if ($option_value){
                    $item->setSku($item->getSku().'-'.$option_value);
                    $order->save();
                }
            }
        }
    }
    public function convertToShorthand($input) {
        // Extract the number using regex
        if (preg_match('/(\d+)\s*year(s)?/i', $input, $matches)) {
            return $matches[1] . 'Y';
        }
        return null;
    }
}
