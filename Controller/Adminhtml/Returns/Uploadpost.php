<?php

namespace Gabrielqs\Boleto\Controller\Adminhtml\Returns;

use \Magento\Backend\App\Action\Context;
use \Magento\Framework\Registry;
use \Magento\Framework\Controller\ResultInterface;
use \Magento\Framework\Exception\LocalizedException;
use \Magento\MediaStorage\Model\File\UploaderFactory;
use \Magento\MediaStorage\Model\File\Uploader;
use \Gabrielqs\Boleto\Controller\Adminhtml\Returns as AbstractReturns;
use \Gabrielqs\Boleto\Helper\Returns\Reader as ReturnsFileReader;


class Uploadpost extends AbstractReturns
{
    /**
     * Allowed file extensions
     * @var string[]
     */
    protected $allowedExtensions = ['ret', 'txt'];

    /**
     * File field name
     * @var string
     */
    protected $fileFieldName = 'file';

    /**
     * Returns File Reader
     * @var ReturnsFileReader
     */
    protected $returnsFileReader;

    /**
     * Upload Factory
     * @var UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * Upload post constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param ReturnsFileReader $returnsFileReader
     * @param UploaderFactory $uploaderFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        ReturnsFileReader $returnsFileReader,
        UploaderFactory $uploaderFactory
    ) {
        $this->uploaderFactory = $uploaderFactory;
        $this->returnsFileReader = $returnsFileReader;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Upload Post action
     * @return ResultInterface
     */
    public function execute()
    {
        $destinationPath = $this->returnsFileReader->getDestinationPath();
        try {
            /** @var Uploader $uploader */
            $uploader = $this->uploaderFactory->create(['fileId' => $this->fileFieldName])
                ->setAllowCreateFolders(true)
                ->setAllowedExtensions($this->allowedExtensions)
                ->addValidateCallback('validate', $this->returnsFileReader, 'validateReturnsFile');

            if (!$uploader->save($destinationPath)) {
                throw new LocalizedException(
                    __('There was an unexpected problem while saving the returns file.')
                );
            }

            if ($this->returnsFileReader->returnsFileExists($uploader->getUploadedFileName())) {
                throw new LocalizedException(
                    __('A file with that name already exists')
                );
            }

            $this->returnsFileReader->readAndSaveReturnsFile($destinationPath . '/' . $uploader->getUploadedFileName());

            $this->messageManager->addSuccessMessage(
                __('The file was successfully saved, please wait a few minutes for it to be processed.')
            );
            $path = 'boleto/returns/index';
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('There was an unexpected problem while saving the returns file.')
            );
            $path = 'boleto/returns/uploadform';
        }
        return $this->_getResultRedirect()->setPath($path);
    }
}
