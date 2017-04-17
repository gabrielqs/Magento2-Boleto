<?php

namespace Gabrielqs\Boleto\Controller\Adminhtml\Remittance;

use \Magento\Framework\View\Result\PageFactory;
use \Magento\Backend\App\Action\Context;
use \Magento\Framework\Registry;
use \Magento\Framework\Controller\ResultInterface;
use \Magento\Backend\Model\View\Result\Page;
use \Gabrielqs\Boleto\Controller\Adminhtml\Remittance as AbstractRemittance;
use \Gabrielqs\Boleto\Controller\Adminhtml\RegistryConstants;
use \Gabrielqs\Boleto\Model\Remittance\FileRepository as RemittanceFileRepository;

class View extends AbstractRemittance
{
    /**
     * Result Page Factory
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Remittance File Repository
     * @var RemittanceFileRepository
     */
    protected $remittanceFileRepository;

    /**
     * Constructor
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param RemittanceFileRepository $remittanceFileRepository
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        RemittanceFileRepository $remittanceFileRepository
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->remittanceFileRepository = $remittanceFileRepository;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * View action
     * @return ResultInterface
     */
    public function execute()
    {
        $remittanceFileId = $this->_getRemittanceFileIdFromRequest();
        try {
            $remittanceFile = $this->remittanceFileRepository->getById($remittanceFileId);

            $this->_coreRegistry->register(RegistryConstants::CURRENT_REMITTANCE_FILE_ID, $remittanceFileId);

            /** @var Page $resultPage */
            $resultPage = $this->resultPageFactory->create();
            $this->initPage($resultPage);
            $this->_prependTitle($resultPage, __('Remittance File %1', $remittanceFile->getName()));
            return $resultPage;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('There was a problem while loading the remittance file.')
            );
            return $this->_getResultRedirect()->setPath('boleto/remittance/index');
        }
    }
}
