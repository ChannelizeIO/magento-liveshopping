<?php

/**
 * Copyright 2022 Channelize.io. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Channelize\LiveShopping\Helper;

class Curl extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Logger
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $_loggerInterface;
    
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Psr\Log\LoggerInterface $loggerInterface
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Psr\Log\LoggerInterface $loggerInterface
    ) {
        parent::__construct($context);
        $this->_loggerInterface = $loggerInterface;
    }
    
    public function curlRequest($url, $method, $data, $privateKey, $userId = "")
    {
        $contentType = 'application/json';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 1);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            [
            'Content-Type: ' . $contentType,
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode($privateKey),
            'User-Id: ' . $userId,
            ]
        );
        
        if ('POST' == $method) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } elseif ('PUT' == $method) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } elseif ('GET' != $method) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        $response = curl_exec($ch);
        
        if ($response === false) {
            $errorNumber = curl_errno($ch);
            $message = curl_error($ch);
            curl_close($ch);
            $this->raiseCurlError($errorNumber, $message);
            return false;
        }

        list($header, $body) = explode("\r\n\r\n", $response, 2);
        if (strpos($header, " 100 Continue") !== false) {
            list($header, $body) = explode("\r\n\r\n", $body, 2);
        }
        
        return json_decode($body, true);
    }
    
    private function raiseCurlError($errorNumber, $message)
    {
        switch ($errorNumber) {
            case CURLE_COULDNT_CONNECT:
            case CURLE_COULDNT_RESOLVE_HOST:
            case CURLE_OPERATION_TIMEOUTED:
                $this->setErrorMessage("Failed to connect Channelize.io ($message).");
                break;
            case CURLE_SSL_CACERT:
            case CURLE_SSL_PEER_CERTIFICATE:
                $this->setErrorMessage("Could not verify Channelize.io SSL certificate.");
                break;
            default:
                $this->setErrorMessage("An unexpected error occurred connecting with Channelize.io.");
                break;
        }
    }

    private function setErrorMessage($errorMessage)
    {
        $this->_loggerInterface->critical($errorMessage);
    }
}
