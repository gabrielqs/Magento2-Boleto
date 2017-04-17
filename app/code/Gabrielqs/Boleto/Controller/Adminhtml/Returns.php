<?php

namespace Gabrielqs\Boleto\Controller\Adminhtml;

use \Magento\Backend\App\Action;
use \Magento\Framework\Registry;
use \Magento\Backend\App\Action\Context;
use \Magento\Backend\Model\View\Result\Page;
use \Magento\Framework\Controller\Result\Redirect;


abstract class Returns extends Action
{
    /**
     * Authorization level of a basic admin session
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Gabrielqs_Boleto::returns_files';

    /**
     * Core registry
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * Constructor
     * @param Context $context
     * @param Registry $coreRegistry
     */
    public function __construct(Context $context, Registry $coreRegistry)
    {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Init page
     * @param Page $resultPage
     * @return Page
     */
    protected function initPage(Page $resultPage)
    {
        $resultPage->setActiveMenu('Gabrielqs_Boleto::remittance_files')
            ->addBreadcrumb(__('Sales'), __('Sales'))
            ->addBreadcrumb(__('Return Files'), __('Return Files'));
        return $resultPage;
    }

    /**
     * Retrieves Returns File Id From Request
     * @return int
     */
    public function _getReturnsFileIdFromRequest()
    {
        return (int) $this->_request->getParam('returns_file_id');
    }

    /**
     * Gets result redirect
     * @return Redirect
     */
    protected function _getResultRedirect()
    {
        return $this->resultRedirectFactory->create();
    }

    /**
     * Prepends Title
     * @param Page $resultPage
     * @param string $title
     * @return Page
     */
    protected function _prependTitle(Page $resultPage, $title)
    {
        $resultPage->getConfig()->getTitle()->prepend($title);
        return $resultPage;
    }
}
