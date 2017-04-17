<?php

namespace Gabrielqs\Boleto\Controller\Adminhtml\Remittance;

use \Magento\Backend\App\Action\Context;
use \Magento\Framework\App\ResponseInterface;
use \Magento\Framework\Registry;
use \Magento\Framework\Controller\ResultInterface;
use \Magento\Framework\App\Response\Http\FileFactory;
use \Gabrielqs\Boleto\Controller\Adminhtml\Remittance as AbstractRemittance;
use \Gabrielqs\Boleto\Model\Remittance\FileRepository as RemittanceFileRepository;

class Download extends AbstractRemittance
{
    /**
     * File Factory
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * Remittance File Repository
     * @var RemittanceFileRepository
     */
    protected $remittanceFileRepository;

    /**
     * Constructor
     * @param Context $context
     * @param Registry $coreRegistry
     * @param FileFactory $fileFactory
     * @param RemittanceFileRepository $remittanceFileRepository
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        FileFactory $fileFactory,
        RemittanceFileRepository $remittanceFileRepository
    ) {
        $this->fileFactory = $fileFactory;
        $this->remittanceFileRepository = $remittanceFileRepository;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Index action
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $remittanceFileId = $this->_getRemittanceFileIdFromRequest();
        try {
            $remittanceFile = $this->remittanceFileRepository->getById($remittanceFileId);
            $remittanceFileName = $remittanceFile->getName();
            $remittanceFileContents = $remittanceFile->getContents();
            $fileResponse = $this->fileFactory->create(
                $remittanceFileName,
                $remittanceFileContents
            );
            return $fileResponse;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('There was a problem while loading the remittance file.')
            );
            return $this->_getResultRedirect()->setPath('boleto/remittance/index');
        }
    }
}
