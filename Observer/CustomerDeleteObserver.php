<?php

/**
 * Copyright 2024 Channelize.io. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Channelize\LiveShopping\Observer;

use Magento\Framework\Event\Observer;

class CustomerDeleteObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Module configuration helper
     *
     * @var \Channelize\LiveShopping\Helper\Config
     */
    protected $_helperConfig;
    
    /**
     * User helper
     *
     * @var \Channelize\LiveShopping\Helper\User
     */
    protected $_helperUser;
    
    /**
     * @param \Channelize\LiveShopping\Helper\Config $helperConfig
     * @param \Channelize\LiveShopping\Helper\User $helperUser
     */
    public function __construct(
        \Channelize\LiveShopping\Helper\Config $helperConfig,
        \Channelize\LiveShopping\Helper\User $helperUser
    ) {
        $this->_helperConfig = $helperConfig;
        $this->_helperUser = $helperUser;
    }
    
    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        
        if ($customer->getId() && $this->_helperConfig->isModuleEnable()) {
            $userData = [
                "id" => (string)$customer->getId()
            ];
            
            $this->_helperUser->deleteUser($userData);
        }
    }
}
