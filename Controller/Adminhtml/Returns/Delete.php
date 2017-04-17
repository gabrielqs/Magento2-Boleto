<?php

namespace Gabrielqs\Boleto\Controller\Adminhtml\Returns;

use \Magento\Backend\App\Action\Context;
use \Magento\Framework\Registry;
use \Magento\Framework\Controller\ResultInterface;
use \Gabrielqs\Boleto\Controller\Adminhtml\Returns as AbstractReturns;
use \Gabrielqs\Boleto\Model\Returns\FileRepository as ReturnsFileRepository;

class Delete extends AbstractReturns
{
    /**
     * Returns File Repository
     * @var ReturnsFileRepository
     */
    protected $returnsFileRepository;

    /**
     * Constructor
     * @param Context $context
     * @param Registry $coreRegistry
     * @param ReturnsFileRepository $returnsFileRepository
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        ReturnsFileRepository $returnsFileRepository
    ) {
        $this->returnsFileRepository = $returnsFileRepository;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Delete action
     * @return ResultInterface
     */
    public function execute()
    {
        $returnsFileId = $this->_getReturnsFileIdFromRequest();
        if ($returnsFileId) {
            try {
                $this->returnsFileRepository->deleteById($returnsFileId);
                $this->messageManager->addSuccessMessage(__('You deleted the return file.'));
                return $this->_getResultRedirect()->setPath('boleto/returns/index');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('There was an error while deleting the file.'));
                return $this->_getResultRedirect()->setPath('boleto/returns/index');
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a return file to delete.'));
        return $this->_getResultRedirect()->setPath('boleto/returns/index');
    }
}
