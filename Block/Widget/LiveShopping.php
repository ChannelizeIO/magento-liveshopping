<?php
/**
 * Copyright 2024 Channelize.io. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Channelize\LiveShopping\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Magento\Framework\View\Element\Template\Context;

class LiveShopping extends Template implements BlockInterface
{
    /**
     * Widget Template File
     *
     * @var string
     */
    protected $_template = "Channelize_LiveShopping::widget/liveshopping.phtml";
    
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
     * Utility helper
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    
    /**
     * Utility helper
     *
     * @var \Magento\Framework\Locale\Resolver
     */
    protected $_localeResolver;
    
    /**
     * Utility helper
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Channelize\LiveShopping\Helper\Config $helperConfig
     * @param \Channelize\LiveShopping\Helper\Utils $helperUtils
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Locale\Resolver $localeResolver
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Channelize\LiveShopping\Helper\Config $helperConfig,
        \Channelize\LiveShopping\Helper\Utils $helperUtils,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\Resolver $localeResolver,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->_helperConfig = $helperConfig;
        $this->_helperUtils = $helperUtils;
        $this->_customerSession = $customerSession;
        $this->_localeResolver = $localeResolver;
        $this->_storeManager = $storeManager;
    }
    
    /**
     * Get is module enable
     *
     * @return bool
     */
    public function isModuleEnable()
    {
        return $this->_helperConfig->isModuleEnable();
    }
    
    /**
     * Get public key
     *
     * @return string
     */
    public function getPublicKey()
    {
        return $this->_helperConfig->getPublicKey();
    }
    
    /**
     * Get store language
     *
     * @return string
     */
    public function getStoreLanguage()
    {
        $haystack  = $this->_localeResolver->getLocale();
        $language = strstr($haystack, '_', true);
        return $language;
    }
    
    /**
     * Get store currency
     *
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();
    }
    
    /**
     * Get customer login
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->_customerSession->isLoggedIn();
    }
    
    /**
     * Get customer id
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->_customerSession->getCustomerId();
    }
    
    /**
     * Get access token
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->_customerSession->getChannelizeAccessToken();
    }
    
    /**
     * Get channelize public key saved in cookies
     *
     * @return string
     */
    public function getCookies()
    {
        return $this->_helperUtils->getPublicCookies("channelize_public_key");
    }
}
