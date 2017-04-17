<?php

namespace Gabrielqs\Boleto\Block\Adminhtml\Returns\View\Tab;

use \Magento\Backend\Block\Widget\Grid;
use \Magento\Backend\Block\Widget\Grid\Extended;
use \Magento\Backend\Block\Template\Context;
use \Magento\Backend\Helper\Data as BackendHelper;
use \Magento\Framework\Registry;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Gabrielqs\Boleto\Model\Returns\File\EventRepository;
use \Gabrielqs\Boleto\Api\Data\ReturnsFileEventInterface;
use \Gabrielqs\Boleto\Controller\Adminhtml\RegistryConstants;
use \Gabrielqs\Boleto\Block\Adminhtml\Returns\View\Tab\Events\DescriptionTranslator;

class Events extends Extended
{
    /**
     * Core Registry
     * @var Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * Returns File Events Repository
     * @var EventRepository|null
     */
    protected $_returnsFileEventsRepository = null;

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
        $this->_returnsFileEventsRepository = $eventsRepository;
        $this->_coreRegistry = $coreRegistry;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
    }


    /**
     * Retrieves current returns file id from registry
     * @return int
     */
    protected function _getReturnsFileId()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_RETURNS_FILE_ID);
    }

    /**
     * Prepares grid collection
     * @return Grid
     */
    protected function _prepareCollection()
    {
        $searchCriteria = $this
            ->_searchCriteriaBuilder
            ->addFilter(ReturnsFileEventInterface::RETURNS_FILE_ID, $this->_getReturnsFileId(), 'eq')
            ->create();
        $collection = $this->_returnsFileEventsRepository->getList($searchCriteria);
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
            ReturnsFileEventInterface::RETURNS_FILE_EVENT_ID,
            [
                'header' => __('Id'),
                'sortable' => true,
                'index' => ReturnsFileEventInterface::RETURNS_FILE_EVENT_ID,
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            ReturnsFileEventInterface::DESCRIPTION,
            [
                'header' => __('Description'),
                'index' => ReturnsFileEventInterface::DESCRIPTION,
                'renderer' => DescriptionTranslator::class
            ]
        );
        $this->addColumn(
            ReturnsFileEventInterface::CREATION_TIME,
            [
                'header' => __('Created'),
                'index' => ReturnsFileEventInterface::CREATION_TIME,
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
        return $this->getUrl('boleto/returns/view', ['_current' => true]);
    }

    /**
     * URL where user is redirected after clicking or filtering the grid.
     * Found no way of making this work properly, leaving with no filter for now
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('boleto/returns/view', ['_current' => true]);
    }

}