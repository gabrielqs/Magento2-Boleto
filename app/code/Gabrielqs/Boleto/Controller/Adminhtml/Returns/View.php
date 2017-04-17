<?php

namespace Gabrielqs\Boleto\Controller\Adminhtml\Returns;

use \Magento\Framework\View\Result\PageFactory;
use \Magento\Backend\App\Action\Context;
use \Magento\Framework\Registry;
use \Magento\Framework\Controller\ResultInterface;
use \Magento\Backend\Model\View\Result\Page;
use \Gabrielqs\Boleto\Controller\Adminhtml\Returns as AbstractReturns;
use \Gabrielqs\Boleto\Controller\Adminhtml\RegistryConstants;
use \Gabrielqs\Boleto\Model\Returns\FileRepository as ReturnsFileRepository;

class View extends AbstractReturns
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
     * View action
     * @return ResultInterface
     */
    public function execute()
    {
        $returnsFileId = $this->_getReturnsFileIdFromRequest();
        try {
            $returnsFile = $this->returnsFileRepository->getById($returnsFileId);

            $this->_coreRegistry->register(RegistryConstants::CURRENT_RETURNS_FILE_ID, $returnsFileId);

            /** @var Page $resultPage */
            $resultPage = $this->resultPageFactory->create();
            $this->initPage($resultPage);
            $this->_prependTitle($resultPage, __('Returns File %1', $returnsFile->getName()));
            return $resultPage;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('There was a problem while loading the returns file.')
            );
            return $this->_getResultRedirect()->setPath('boleto/returns/index');
        }
    }
}
