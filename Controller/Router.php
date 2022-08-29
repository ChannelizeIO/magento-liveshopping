<?php
/**
 * Copyright 2022 Channelize.io. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Channelize\LiveShopping\Controller;

class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    private $_actionFactory;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;
    
    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    private $_pageFactory;

    /**
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Cms\Model\PageFactory $pageFactory
    ) {
        $this->_actionFactory = $actionFactory;
        $this->_storeManager = $storeManagerInterface;
        $this->_pageFactory = $pageFactory;
    }
    
    /**
     * Handle Custom Route
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Cms\Api\GetPageByIdentifierInterface
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        try {
            $identifier = trim($request->getPathInfo(), '/');

            if (strpos($identifier, 'streams/') !== false) {
                $storeId = $this->_storeManager->getStore()->getId();
                $page = $this->_pageFactory->create();
                $pageId = $page->checkIdentifier("streams", $storeId);
                if ($pageId) {
                    $request->setModuleName('cms')
                        ->setControllerName('page')
                        ->setActionName('view')
                        ->setParam('page_id', $pageId);
                }
            } else {
                return;
            }

            return $this
                ->_actionFactory
                ->create(\Magento\Framework\App\Action\Forward::class, ['request' => $request]);
        } catch (\Exception $exception) {
            return;
        }
    }
}
