<?php
/**
 * Copyright 2024 Channelize.io. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Channelize\LiveShopping\Controller\Adminhtml\Instruction;

use \Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class Page extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;
    
    /**
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Stream page instruction controller.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Channelize_LiveShopping::liveshopping_stream_page_instruction');
        $resultPage->getConfig()->getTitle()->prepend(__('Channelize.io Live Shopping & Video Streams'));
        return $resultPage;
    }
}
