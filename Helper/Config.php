<?php

/**
 * Copyright 2021 Channelize.io. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Channelize\LiveShopping\Helper;

use Magento\Store\Model\ScopeInterface;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Get module status
     *
     * @return bool
     */
    public function getModuleStatus()
    {
        return $this->scopeConfig->getValue(
            'channelizeliveshopping/configuration/status',
            ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Get public key
     *
     * @return string
     */
    public function getPublicKey()
    {
        return $this->scopeConfig->getValue(
            'channelizeliveshopping/configuration/public_key',
            ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Get private key
     *
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->scopeConfig->getValue(
            'channelizeliveshopping/configuration/private_key',
            ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Get module status
     *
     * @return bool
     */
    public function isModuleEnable()
    {
        return $this->getModuleStatus() && $this->getPublicKey() && $this->getPrivateKey();
    }
}
