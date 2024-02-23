<?php

/**
 * Copyright 2024 Channelize.io. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Channelize\LiveShopping\Helper;

class User extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Channelize\LiveShopping\Helper\Config
     */
    private $_helperConfig;
    
    /**
     * @var \Channelize\LiveShopping\Helper\Curl
     */
    private $_helperCurl;
    
    const USER_API_URL = "https://api.channelize.io/v2/users/";
    
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Channelize\LiveShopping\Helper\Config $helperConfig
     * @param \Channelize\LiveShopping\Helper\Curl $helperCurl
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Channelize\LiveShopping\Helper\Config $helperConfig,
        \Channelize\LiveShopping\Helper\Curl $helperCurl
    ) {
        parent::__construct($context);
        $this->_helperConfig = $helperConfig;
        $this->_helperCurl = $helperCurl;
    }
    
    /**
     * Create access token
     *
     * @param array $data
     * @return array
     */
    public function createAccessToken($data)
    {
        $response = $this->_helperCurl->curlRequest(
            self::USER_API_URL . "create_access_token",
            'POST',
            json_encode($data),
            $this->_helperConfig->getPrivateKey()
        );
        return $response;
    }
    
    /**
     * Create user
     *
     * @param array $data
     * @return array
     */
    public function createUser($data)
    {
        $response = $this->_helperCurl->curlRequest(
            self::USER_API_URL,
            'POST',
            json_encode($data),
            $this->_helperConfig->getPrivateKey()
        );
        return $response;
    }
    
    /**
     * Update user
     *
     * @param array $data
     * @return array
     */
    public function updateUser($data)
    {
        $response = $this->_helperCurl->curlRequest(
            self::USER_API_URL . $data["id"],
            'PUT',
            json_encode($data),
            $this->_helperConfig->getPrivateKey()
        );
        return $response;
    }
    
    /**
     * Delete user
     *
     * @param array $data
     * @return array
     */
    public function deleteUser($data)
    {
        $response = $this->_helperCurl->curlRequest(
            self::USER_API_URL . $data["id"],
            'DELETE',
            null,
            $this->_helperConfig->getPrivateKey()
        );
        return $response;
    }
    
    /**
     * Logout user
     *
     * @param array $data
     * @return array
     */
    public function logoutUser($data)
    {
        $response = $this->_helperCurl->curlRequest(
            self::USER_API_URL . "logout",
            'POST',
            json_encode(
                [
                "accessToken" => $data["accessToken"]
                ]
            ),
            $this->_helperConfig->getPrivateKey(),
            $data["id"]
        );
        return $response;
    }
}
