<?php

namespace Gabrielqs\Boleto\Controller\Adminhtml\Returns;

use \Magento\Backend\App\Action\Context;
use \Magento\Framework\App\ResponseInterface;
use \Magento\Framework\Registry;
use \Magento\Framework\Controller\ResultInterface;
use \Magento\Framework\App\Response\Http\FileFactory;
use \Gabrielqs\Boleto\Controller\Adminhtml\Returns as AbstractReturns;
use \Gabrielqs\Boleto\Model\Returns\FileRepository as ReturnsFileRepository;

class Download extends AbstractReturns
{
    /**
     * File Factory
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * Returns File Repository
     * @var ReturnsFileRepository
     */
    protected $returnsFileRepository;

    /**
     * Constructor
     * @param Context $context
     * @param Registry $coreRegistry
     * @param FileFactory $fileFactory
     * @param ReturnsFileRepository $returnsFileRepository
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        FileFactory $fileFactory,
        ReturnsFileRepository $returnsFileRepository
    ) {
        $this->fileFactory = $fileFactory;
        $this->returnsFileRepository = $returnsFileRepository;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Index action
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $returnsFileId = $this->_getReturnsFileIdFromRequest();
        try {
            $returnsFile = $this->returnsFileRepository->getById($returnsFileId);
            $returnsFileName = $returnsFile->getName();
            $returnsFileContents = $returnsFile->getContents();
            $fileResponse = $this->fileFactory->create(
                $returnsFileName,
                $returnsFileContents
            );
            return $fileResponse;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('There was a problem while loading the returns file.')
            );
            return $this->_getResultRedirect()->setPath('boleto/returns/index');
        }
    }
}
