<?php
/**
 * Copyright 2024 Channelize.io. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Channelize\LiveShopping\Block;

use Magento\Framework\View\Element\Template\Context;

class Success extends \Magento\Framework\View\Element\Template
{

    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderRepository $order
    ) {
        parent::__construct($context);
        $this->_checkoutSession = $checkoutSession;
        $this->_order = $order;
    }
    
    public function getOrderDetails($orderId) {
        $order = $this->_order->get($orderId);
        $orderItems = $order->getAllItems();
        
        $orderItemsDetails = [];
        foreach ($orderItems as $item) {
            array_push($orderItemsDetails, $item->getData());
        }
        
        return [
            "orderId" => $order->getId(),
            "currencyCode" => $order->getOrderCurrencyCode(),
            "products" => $orderItemsDetails,
            "customerId" => $order->getCustomerId()
        ];
    }
    
    public function getOrderId() {
        return  $this->_checkoutSession->getLastRealOrderId();
    }
}
