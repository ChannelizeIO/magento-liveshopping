<?php

/**
 * Copyright 2021 Channelize.io. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Channelize\LiveShopping\Observer;

use Magento\Framework\Event\Observer;

class AdminSystemConfigSaverObserver implements \Magento\Framework\Event\ObserverInterface
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
     * @var \Magento\Cms\Model\PageFactory
     */
    private $_pageFactory;
    
    /**
     * @param \Channelize\LiveShopping\Helper\Config $helperConfig
     * @param \Channelize\LiveShopping\Helper\Utils $helperUtils
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     */
    public function __construct(
        \Channelize\LiveShopping\Helper\Config $helperConfig,
        \Channelize\LiveShopping\Helper\Utils $helperUtils,
        \Magento\Cms\Model\PageFactory $pageFactory
    ) {
        $this->_helperConfig = $helperConfig;
        $this->_helperUtils = $helperUtils;
        $this->_pageFactory = $pageFactory;
    }
    
    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        $configData = $observer->getEvent()->getData();
        $stores = $configData["store"] ? $configData["store"] : 0;

        $page = $this->_pageFactory->create();
        $pageId = $page->checkIdentifier("streams", $stores);
        
        if (!$pageId) {
            $cmsPageData = [
                'title' => 'Streams',
                'page_layout' => '1column',
                'meta_keywords' => 'Live Stream Shopping',
                'meta_description' => '',
                'identifier' => 'streams',
                'content_heading' => '',
                'content' => '<div>{{widget type="Channelize\LiveShopping\Block\Widget\LiveShopping"}}</div>',
                'is_active' => 1,
                'stores' => [$stores],
                'sort_order' => 0,
                "layout_update_xml" => '
                    <referenceContainer name="content">
                        <block class="Magento\Cms\Block\Page" name="cms_page" cacheable="false"/>
                    </referenceContainer>'
            ];

            // create page
            $this->_pageFactory->create()->setData($cmsPageData)->save();
        }
    }
}
