<?php

namespace Gabrielqs\Boleto\Block\Adminhtml\Remittance\View\Tab;

use \Magento\Backend\Block\Widget\Grid;
use \Magento\Backend\Block\Widget\Grid\Extended;
use \Magento\Backend\Block\Template\Context;
use \Magento\Backend\Helper\Data as BackendHelper;
use \Magento\Framework\Registry;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Gabrielqs\Boleto\Model\Remittance\File\EventRepository;
use \Gabrielqs\Boleto\Api\Data\RemittanceFileEventInterface;
use \Gabrielqs\Boleto\Controller\Adminhtml\RegistryConstants;
use \Gabrielqs\Boleto\Block\Adminhtml\Remittance\View\Tab\Events\DescriptionTranslator;

class Events extends Extended
{
    /**
     * Core Registry
     * @var Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * Remittance File Events Repository
     * @var EventRepository|null
     */
    protected $_remittanceFileEventsRepository = null;

    /**
     * Search Criteria Builder
     * @var SearchCriteriaBuilder|null
     */
    protected $_searchCriteriaBuilder = null;

    /**
     * Constructor
     * @param Context $context
     * @param BackendHelper $backendHelper
     * @param EventRepository $eventsRepository
     * @param Registry $coreRegistry
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     */
    public function __construct(
        Context $context,
        BackendHelper $backendHelper,
        EventRepository $eventsRepository,
        Registry $coreRegistry,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->_remittanceFileEventsRepository = $eventsRepository;
        $this->_coreRegistry = $coreRegistry;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
    }


    /**
     * Retrieves current remittance file id from registry
     * @return int
     */
    protected function _getRemittanceFileId()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_REMITTANCE_FILE_ID);
    }

    /**
     * Prepares grid collection
     * @return Grid
     */
    protected function _prepareCollection()
    {
        $searchCriteria = $this
            ->_searchCriteriaBuilder
            ->addFilter(RemittanceFileEventInterface::REMITTANCE_FILE_ID, $this->_getRemittanceFileId(), 'eq')
            ->create();
        $collection = $this->_remittanceFileEventsRepository->getList($searchCriteria);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Pepares Columns
     * @return Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            RemittanceFileEventInterface::REMITTANCE_FILE_EVENT_ID,
            [
                'header' => __('Id'),
                'sortable' => true,
                'index' => RemittanceFileEventInterface::REMITTANCE_FILE_EVENT_ID,
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            RemittanceFileEventInterface::DESCRIPTION,
            [
                'header' => __('Description'),
                'index' => RemittanceFileEventInterface::DESCRIPTION,
                'renderer' => DescriptionTranslator::class
            ]
        );
        $this->addColumn(
            RemittanceFileEventInterface::CREATION_TIME,
            [
                'header' => __('Created'),
                'index' => RemittanceFileEventInterface::CREATION_TIME,
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * URL where user is redirected after clicking or filtering the grid.
     * Found no way of making this work properly, leaving with no filter for now
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('boleto/remittance/view', ['_current' => true]);
    }

    /**
     * URL where user is redirected after clicking or filtering the grid.
     * Found no way of making this work properly, leaving with no filter for now
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('boleto/remittance/view', ['_current' => true]);
    }

}