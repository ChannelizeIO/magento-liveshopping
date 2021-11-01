<?php

/**
 * Copyright 2021 Channelize.io. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Channelize\LiveShopping\Observer;

use Magento\Framework\Event\Observer;

class CustomerLogoutObserver implements \Magento\Framework\Event\ObserverInterface
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
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    
    /**
     * @param \Channelize\LiveShopping\Helper\Config $helperConfig
     * @param \Channelize\LiveShopping\Helper\User $helperUser
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Channelize\LiveShopping\Helper\Config $helperConfig,
        \Channelize\LiveShopping\Helper\User $helperUser,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_helperConfig = $helperConfig;
        $this->_helperUser = $helperUser;
        $this->_customerSession = $customerSession;
    }
    
    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        
        if ($customer->getId() && $this->_helperConfig->isModuleEnable()) {
            $userData = [
                "id" => $this->_customerSession->getCustomer()->getId(),
                "accessToken" => (string)$this->_customerSession->getData("channelize_access_token")
            ];

            $this->_helperUser->logoutUser($userData);
        }
    }
}
