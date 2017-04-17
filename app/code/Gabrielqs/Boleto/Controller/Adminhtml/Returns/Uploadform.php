<?php

namespace Gabrielqs\Boleto\Controller\Adminhtml\Returns;

use \Magento\Framework\View\Result\PageFactory;
use \Magento\Backend\App\Action\Context;
use \Magento\Framework\Registry;
use \Magento\Framework\Controller\ResultInterface;
use \Gabrielqs\Boleto\Controller\Adminhtml\Returns as AbstractReturns;
use \Gabrielqs\Boleto\Model\Returns\FileRepository as ReturnsFileRepository;

class Uploadform extends AbstractReturns
{
    /**
     * Result Page Factory
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Returns File Repository
     * @var ReturnsFileRepository
     */
    protected $returnsFileRepository;

    /**
     * Constructor
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param ReturnsFileRepository $returnsFileRepository
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        ReturnsFileRepository $returnsFileRepository
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->returnsFileRepository = $returnsFileRepository;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Upload Form action
     * @return ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->getConfig()->getTitle()->prepend(__('Upload Return File'));
        return $resultPage;
    }
}
