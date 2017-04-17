<?php

namespace Gabrielqs\Boleto\Controller\Adminhtml\Returns;

use \Magento\Framework\View\Result\LayoutFactory;
use \Magento\Framework\View\Result\Layout;
use \Magento\Backend\App\Action\Context;
use \Magento\Framework\Registry;
use \Magento\Framework\View\Result\PageFactory;
use \Gabrielqs\Boleto\Controller\Adminhtml\RegistryConstants;
use \Gabrielqs\Boleto\Controller\Adminhtml\Returns as AbstractReturns;
use \Gabrielqs\Boleto\Model\Returns\FileRepository as ReturnsFileRepository;


class ViewOrders extends AbstractReturns
{
    /**
     * Result Page Factory
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Result Layout Factory
     * @var LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * Returns File Repository
     * @var ReturnsFileRepository
     */
    protected $returnsFileRepository;

    /**
     * Constructor
     * @param Context $context
     * @param Registry $registry
     * @param LayoutFactory $resultLayoutFactory
     * @param PageFactory $resultPageFactory
     * @param ReturnsFileRepository $returnsFileRepository
     */
    public function __construct(
        Context $context,
        Registry $registry,
        LayoutFactory $resultLayoutFactory,
        PageFactory $resultPageFactory,
        ReturnsFileRepository $returnsFileRepository
    ) {
        parent::__construct($context, $registry);
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->returnsFileRepository = $returnsFileRepository;
    }

    /**
     * Execute
     * @return Layout
     */
    public function execute()
    {
        $returnsFileId = $this->_request->getParam('returns_file_id');
        $this->returnsFileRepository->getById($returnsFileId);
        $this->_coreRegistry->register(RegistryConstants::CURRENT_RETURNS_FILE_ID, $returnsFileId);
        $resultLayout = $this->resultLayoutFactory->create();
        return $resultLayout;
    }
}