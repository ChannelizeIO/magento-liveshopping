<?php

/**
 * Copyright 2021 Channelize.io. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Channelize\LiveShopping\Helper;

class Utils extends \Magento\Framework\App\Helper\AbstractHelper
{
    
    /**
     * @var \Channelize\LiveShopping\Helper\Config
     */
    private $_helperConfig;
    
    /**
     * @var \Channelize\LiveShopping\Helper\User
     */
    private $_helperUser;
    
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $_customerSession;
    
    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $_cookieMetadataFactory;
    
    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    private $_cookieManagerInterface;
    
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Channelize\LiveShopping\Helper\Config $helperConfig
     * @param \Channelize\LiveShopping\Helper\User $helperUser
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManagerInterface
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Channelize\LiveShopping\Helper\Config $helperConfig,
        \Channelize\LiveShopping\Helper\User $helperUser,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManagerInterface
    ) {
        parent::__construct($context);
        $this->_helperConfig = $helperConfig;
        $this->_helperUser = $helperUser;
        $this->_customerSession = $customerSession;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        $this->_cookieManagerInterface = $cookieManagerInterface;
    }
    
    /**
     * Get product basic details
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @return void
     */
    public function loginCustomerOnChannelize($customer)
    {
        if ($this->_helperConfig->isModuleEnable()) {
            $userData = [
                "userId" => (string)$customer->getId()
            ];

            $response = $this->_helperUser->createAccessToken($userData);
            
            if (isset($response["id"])) {
                // Set the access token in session
                $this->_customerSession->setChannelizeAccessToken($response["id"]);
                
                // Set public key in cookies.
                $this->setPublicCookies("channelize_public_key", $this->_helperConfig->getPublicKey());
            } elseif (isset($response["error"]) && isset($response["error"]["code"]) &&
                    $response["error"]["code"] == "NO_RECORD_FOUND") {
                // Create new user
                $newUserData = [
                    "id" => (string)$customer->getId(),
                    "displayName" => $customer->getName()
                ];
                
                $this->_helperUser->createUser($newUserData);
                
                // Create access token
                $response = $this->_helperUser->createAccessToken($userData);
                if (isset($response["id"])) {
                    // Set the access token in session
                    $this->_customerSession->setChannelizeAccessToken($response["id"]);
                    
                    // Set public key in cookies.
                    $this->setPublicCookies("channelize_public_key", $this->_helperConfig->getPublicKey());
                }
            }
        }
    }
    
    /**
     * Set public cookie
     *
     * @param string $cookieName
     * @param string $cookieValue
     * @return void
     */
    public function setPublicCookies($cookieName, $cookieValue)
    {
        // Delete cookies
        $metadata = $this->_cookieMetadataFactory->createPublicCookieMetadata();
        $metadata->setPath('/');
        $this->_cookieManagerInterface->deleteCookie($cookieName, $metadata);
        
        // Create cookies
        $publicCookieMetadata = $this->_cookieMetadataFactory->createPublicCookieMetadata();
        $publicCookieMetadata->setDurationOneYear();
        $publicCookieMetadata->setPath('/');
        $publicCookieMetadata->setHttpOnly(false);

        $this->_cookieManagerInterface->setPublicCookie(
            $cookieName,
            $cookieValue,
            $publicCookieMetadata
        );
    }
    
    /**
     * Get public cookie
     *
     * @param string $cookieName
     * @return void
     */
    public function getPublicCookies($cookieName)
    {
        return $this->_cookieManagerInterface->getCookie(
            $cookieName
        );
    }
}
