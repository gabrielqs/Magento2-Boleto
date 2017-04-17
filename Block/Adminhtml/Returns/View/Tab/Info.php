<?php

namespace Gabrielqs\Boleto\Block\Adminhtml\Returns\View\Tab;

use \Magento\Backend\Block\Template;
use \Magento\Backend\Block\Template\Context;
use \Magento\Framework\Registry;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Gabrielqs\Boleto\Model\Returns\File;
use \Gabrielqs\Boleto\Model\Returns\FileRepository;
use \Gabrielqs\Boleto\Controller\Adminhtml\RegistryConstants;
use \Gabrielqs\Boleto\Helper\Returns\Data as BoletoReturnsHelper;

/**
 * Adminhtml returns file view information block.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Info extends Template
{
    /**
     * Boleto Return sHelper
     * @var BoletoReturnsHelper
     */
    protected $boletoReturnsHelper = null;

    /**
     * Core registry
     * @var Registry
     */
    protected $coreRegistry = null;

    /**
     * Returns File Repository
     * @var FileRepository|null
     */
    protected $returnsFileRepository = null;

    /**
     * Returns File Repository
     * @var File|null
     */
    protected $returnsFile = null;

    /**
     * Search Criteria Builder
     * @var SearchCriteriaBuilder|null
     */
    protected $searchCriteriaBuilder = null;

    /**
     * Constructor
     * @param Context $context
     * @param Registry $registry
     * @param FileRepository $returnsFileRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param BoletoReturnsHelper $boletoReturnsHelper
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FileRepository $returnsFileRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        BoletoReturnsHelper $boletoReturnsHelper,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->returnsFileRepository = $returnsFileRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->boletoReturnsHelper = $boletoReturnsHelper;
        parent::__construct($context, $data);
    }

    /**
     * Retrieves current returns file id from registry
     * @return int
     */
    protected function _getReturnsFileId()
    {
        return $this->coreRegistry->registry(RegistryConstants::CURRENT_RETURNS_FILE_ID);
    }

    /**
     * Retrieves current returns file
     * @return File
     */
    protected function _getReturnsFile()
    {
        if ($this->returnsFile === null) {
            $this->returnsFile = $this->returnsFileRepository->getById($this->_getReturnsFileId());
        }
        return $this->returnsFile;
    }

    public function getFileName() {
        return $this->_getReturnsFile()->getName();
    }

    public function getStatus() {
        return $this->boletoReturnsHelper->getStatusLabel($this->_getReturnsFile()->getStatus());
    }

    public function getCreationTime() {
        return $this->_getReturnsFile()->getCreationTime();
    }

    public function getUpdateTime() {
        return $this->_getReturnsFile()->getUpdateTime();
    }
}
