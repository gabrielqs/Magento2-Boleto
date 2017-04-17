<?php

namespace Gabrielqs\Boleto\Block\Adminhtml\Remittance\View\Tab;

use \Magento\Backend\Block\Template;
use \Magento\Backend\Block\Template\Context;
use \Magento\Framework\Registry;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Gabrielqs\Boleto\Model\Remittance\File;
use \Gabrielqs\Boleto\Model\Remittance\FileRepository;
use \Gabrielqs\Boleto\Controller\Adminhtml\RegistryConstants;
use \Gabrielqs\Boleto\Helper\Remittance\Data as BoletoRemittanceHelper;

/**
 * Adminhtml remittance file view information block.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Info extends Template
{
    /**
     * Boleto Return sHelper
     * @var BoletoRemittanceHelper
     */
    protected $boletoRemittanceHelper = null;

    /**
     * Core registry
     * @var Registry
     */
    protected $coreRegistry = null;

    /**
     * Remittance File Repository
     * @var FileRepository|null
     */
    protected $remittanceFileRepository = null;

    /**
     * Remittance File Repository
     * @var File|null
     */
    protected $remittanceFile = null;

    /**
     * Search Criteria Builder
     * @var SearchCriteriaBuilder|null
     */
    protected $searchCriteriaBuilder = null;

    /**
     * Constructor
     * @param Context $context
     * @param Registry $registry
     * @param FileRepository $remittanceFileRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param BoletoRemittanceHelper $boletoRemittanceHelper
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FileRepository $remittanceFileRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        BoletoRemittanceHelper $boletoRemittanceHelper,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->remittanceFileRepository = $remittanceFileRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->boletoRemittanceHelper = $boletoRemittanceHelper;
        parent::__construct($context, $data);
    }

    /**
     * Retrieves current remittance file id from registry
     * @return int
     */
    protected function _getRemittanceFileId()
    {
        return $this->coreRegistry->registry(RegistryConstants::CURRENT_REMITTANCE_FILE_ID);
    }

    /**
     * Retrieves current remittance file
     * @return File
     */
    protected function _getRemittanceFile()
    {
        if ($this->remittanceFile === null) {
            $this->remittanceFile = $this->remittanceFileRepository->getById($this->_getRemittanceFileId());
        }
        return $this->remittanceFile;
    }

    public function getFileName() {
        return $this->_getRemittanceFile()->getName();
    }

    public function getStatus() {
        return $this->boletoRemittanceHelper->getStatusLabel($this->_getRemittanceFile()->getStatus());
    }

    public function getCreationTime() {
        return $this->_getRemittanceFile()->getCreationTime();
    }

    public function getUpdateTime() {
        return $this->_getRemittanceFile()->getUpdateTime();
    }
}
