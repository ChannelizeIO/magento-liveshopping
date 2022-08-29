<?php
/**
 * Copyright 2022 Channelize.io. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Channelize\LiveShopping\Controller\Carts;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Checkout\Model\Cart as CustomerCart;

class AddToCart extends Action
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $_productRepository;
    
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $_cart;
    
    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    private $_productAttributeRepository;
    
    /**
     * @param Context $context
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param CustomerCart $cart
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository
     */
    public function __construct(
        Context $context,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        CustomerCart $cart,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository
    ) {
        parent::__construct($context);
        $this->_productRepository = $productRepository;
        $this->_cart = $cart;
        $this->_productAttributeRepository = $productAttributeRepository;
    }
    
    /**
     * @inheritdoc
     */
    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $response = [
            "success" => false
        ];
        try {
            $productData = $this->getRequest()->getPostValue("data");
            
            if (!isset($productData)) {
                return $resultJson->setData($response);
            }
            
            $productDataArray = json_decode($productData, true);
            
            if (isset($productDataArray[0]) && isset($productDataArray[0]["product"])) {
                $data = $productDataArray[0];
                // Get Product
                $product = $this->_productRepository->getById($data["product"]);
                
                // Modify the configurable super attribute data
                if ($product->getTypeId() == "configurable" && isset($data["super_attribute"])) {
                    $data["super_attribute"] = $this->modifiedSuperAttribute($data["super_attribute"]);
                }
                
                // Add to cart
                $this->_cart->addProduct(
                    $product,
                    $data
                );
                $this->_cart->save();

                $response["success"] = true;
            }
            return $resultJson->setData($response);
            
        } catch (\Exception $exception) {
            $response["message"] = $exception->getMessage();
            return $resultJson->setData($response);
        }
    }
    
    /**
     * Modify configurable product super attribute
     *
     * @param array $superAttribute
     * @return array
     */
    protected function modifiedSuperAttribute($superAttribute)
    {
        $modifiedSuperAttribute = [];
        foreach ($superAttribute as $key => $value) {
            $attribute = $this->_productAttributeRepository->get($key);
            $id = $attribute->getAttributeId();
            $modifiedSuperAttribute[$id] = $value;
        }
        return $modifiedSuperAttribute;
    }
}
