<?php
/**
 * Copyright 2024 Channelize.io. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Channelize\LiveShopping\Controller\Carts;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class CartDetails extends Action
{
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $_cart;
    
    /**
     * @param Context $context
     * @param \Magento\Checkout\Model\Cart $cart
     */
    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Cart $cart
    ) {
        parent::__construct($context);
        $this->_cart = $cart;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $response = [
            "success" => false,
            "cart" => [
                "items" => []
            ]
        ];
        try {
            // Cart Item
            $itemsVisible = $this->_cart->getQuote()->getAllVisibleItems();
            foreach ($itemsVisible as $item) {
                $response["cart"]["items"][] = [
                    "item_id" => $item->getItemId(),
                    "product_id" => $item->getProductId(),
                    "sku"=> $item->getSku(),
                    "name" => $item->getName(),
                    "product_type" => $item->getProductType(),
                    "price" => $item->getPrice(),
                    "qty" => $item->getQty(),
                    "total" => $item->getRowTotal()
                ];
            }
            
            // Cart Total Quantity
            $totalQuantity = $this->_cart->getQuote()->getItemsQty();
            $response["cart"]["total_quantity"] = (int)$totalQuantity;
            
            // Cart Total
            $subTotal = $this->_cart->getQuote()->getSubtotal();
            $grandTotal = $this->_cart->getQuote()->getGrandTotal();
            $response["cart"]["sub_total"] = (float)$subTotal;
            $response["cart"]["discount"] = (float)($subTotal - $grandTotal);
            $response["cart"]["grand_total"] = (float)$grandTotal;
        
            $response["success"] = true;
            return $resultJson->setData($response);
        } catch (\Exception $exception) {
            $response["message"] = $exception->getMessage();
            return $resultJson->setData($response);
        }
    }
}
