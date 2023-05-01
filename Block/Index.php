<?php

namespace Fahim\ProductFaq\Block;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\Product;

class Index extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        Context $context,
        Registry $registry,
        Product $product,
        ProductRepository $productRepository,
        array $data = []
    ) {
        $this->_registry = $registry;
        $this->product = $product;
        $this->productRepository = $productRepository;
        parent::__construct($context, $data);
    }

     public function _prepareLayout(){
        return parent::_prepareLayout();
    }
 
    public function getCurrentProductSku(){
        return $this->_registry->registry('current_product')->getSku();
    }

     public function getCurrentProduct(){

        $sku = $this->getCurrentProductSku();
        try {
            $product = $this->productRepository->get($sku);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('Such product doesn\'t exist'));
        }
        return $product;
    }
}