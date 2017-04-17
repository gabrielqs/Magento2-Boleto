<?php

namespace Gabrielqs\Boleto\Controller\Adminhtml\Remittance;

use \Magento\Framework\View\Result\LayoutFactory;
use \Magento\Framework\View\Result\Layout;
use \Magento\Backend\App\Action\Context;
use \Magento\Framework\Registry;
use \Magento\Framework\View\Result\PageFactory;
use \Gabrielqs\Boleto\Controller\Adminhtml\RegistryConstants;
use \Gabrielqs\Boleto\Controller\Adminhtml\Remittance as AbstractRemittance;
use \Gabrielqs\Boleto\Model\Remittance\FileRepository as RemittanceFileRepository;


class ViewEvents extends AbstractRemittance
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
     * Remittance File Repository
     * @var RemittanceFileRepository
     */
    protected $remittanceFileRepository;

    /**
     * Constructor
     * @param Context $context
     * @param Registry $registry
     * @param LayoutFactory $resultLayoutFactory
     * @param PageFactory $resultPageFactory
     * @param RemittanceFileRepository $remittanceFileRepository
     */
    public function __construct(
        Context $context,
        Registry $registry,
        LayoutFactory $resultLayoutFactory,
        PageFactory $resultPageFactory,
        RemittanceFileRepository $remittanceFileRepository
    ) {
        parent::__construct($context, $registry);
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->remittanceFileRepository = $remittanceFileRepository;
    }

    /**
     * Execute
     * @return Layout
     */
    public function execute()
    {
        $remittanceFileId = $this->_request->getParam('remittance_file_id');
        $this->remittanceFileRepository->getById($remittanceFileId);
        $this->_coreRegistry->register(RegistryConstants::CURRENT_REMITTANCE_FILE_ID, $remittanceFileId);
        $resultLayout = $this->resultLayoutFactory->create();
        return $resultLayout;
    }
}