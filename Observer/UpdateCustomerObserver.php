<?php

/**
 * Copyright 2022 Channelize.io. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Channelize\LiveShopping\Observer;

use Magento\Framework\Event\Observer;

class UpdateCustomerObserver implements \Magento\Framework\Event\ObserverInterface
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
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    
    /**
     * Customer model
     *
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customer;
    
    /**
     * @param \Channelize\LiveShopping\Helper\Config $helperConfig
     * @param \Channelize\LiveShopping\Helper\User $helperUser
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    public function __construct(
        \Channelize\LiveShopping\Helper\Config $helperConfig,
        \Channelize\LiveShopping\Helper\User $helperUser,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->_helperConfig = $helperConfig;
        $this->_helperUser = $helperUser;
        $this->_storeManager = $storeManagerInterface;
        $this->_customerFactory = $customerFactory;
    }
    
    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        $email = $observer->getEvent()->getData("email");
        
        if ($email && $this->_helperConfig->isModuleEnable()) {
            $websiteId = $this->_storeManager->getWebsite()->getWebsiteId();
            $customer = $this->_customerFactory->create();
            $customer->setWebsiteId($websiteId);
            $customer->loadByEmail($email);
            
            if ($customer->getId()) {
                $userData = [
                    "id" => (string)$customer->getId(),
                    "displayName" => $customer->getName()
                ];

                $this->_helperUser->updateUser($userData);
            }
        }
    }
}
