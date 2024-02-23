<?php
/**
 * Copyright 2024 Channelize.io. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Channelize\LiveShopping\Block\Adminhtml\System\Config;

class IntegrationHelper extends \Magento\Config\Block\System\Config\Form\Field
{
    
    /**
     * Get helper content
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return '
        <div class="notices-wrapper">
            <div class="messages">
                <div class="message" style="margin-top: 20px;">
                    <p>' . __('Channelize.io issues Public and Private Keys to app developers in order for them to identify their applications and access API.') . '</p>
                    <p>' . __('After signing up at <a href="%1" target="_blank">Live Shopping & Video Streams Dashboard</a>, you can create multiple applications, each with its own Public key and Private key. Your Public and Private key can be found on the <b>Help & Support</b> page of your Dashboard.', 'https://channelize.io/channelize/lsc/account/login/') . '</p>
                </div>
            </div>
        </div>';
    }
}
