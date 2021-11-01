<?php

/**
 * Copyright 2021 Channelize.io. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Channelize\LiveShopping\Observer;

use Magento\Framework\Event\Observer;

class CustomerLoginObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Module configuration helper
     *
     * @var \Channelize\LiveShopping\Helper\Config
     */
    protected $_helperConfig;
    
    /**
     * Utility helper
     *
     * @var \Channelize\LiveShopping\Helper\Utils
     */
    protected $_helperUtils;
    
    /**
     * @param \Channelize\LiveShopping\Helper\Config $helperConfig
     * @param \Channelize\LiveShopping\Helper\Utils $helperUtils
     */
    public function __construct(
        \Channelize\LiveShopping\Helper\Config $helperConfig,
        \Channelize\LiveShopping\Helper\Utils $helperUtils
    ) {
        $this->_helperConfig = $helperConfig;
        $this->_helperUtils = $helperUtils;
    }
    
    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        
        if ($this->_helperConfig->isModuleEnable()) {
            $this->_helperUtils->loginCustomerOnChannelize($customer);
        }
    }
}
