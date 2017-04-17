<?php

namespace Gabrielqs\Boleto\Controller\Adminhtml\Remittance;

use \Magento\Framework\View\Result\PageFactory;
use \Magento\Backend\App\Action\Context;
use \Magento\Framework\Registry;
use \Magento\Framework\Controller\ResultInterface;
use \Magento\Backend\Model\View\Result\Page;
use \Gabrielqs\Boleto\Controller\Adminhtml\Remittance as AbstractRemittance;

class Index extends AbstractRemittance
{
    /**
     * Result Page Factory
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Constructor
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Index action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->getConfig()->getTitle()->prepend(__('Remittance Files'));

        return $resultPage;
    }
}
