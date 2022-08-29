<?php
/**
 * Copyright 2022 Channelize.io. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Channelize\LiveShopping\Controller\Adminhtml\Instruction;

use \Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class Setup extends Action
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
     * Setup page instruction controller.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Channelize_LiveShopping::liveshopping_setup_instruction');
        $resultPage->getConfig()->getTitle()->prepend(__('Channelize.io Live Shopping & Video Streams'));
        return $resultPage;
    }
}
