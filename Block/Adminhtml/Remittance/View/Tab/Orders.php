<?php

namespace Gabrielqs\Boleto\Block\Adminhtml\Remittance\View\Tab;

use \Magento\Backend\Block\Widget\Grid;
use \Magento\Backend\Block\Widget\Grid\Extended;
use \Magento\Backend\Block\Template\Context;
use \Magento\Backend\Helper\Data as BackendHelper;
use \Magento\Framework\Registry;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Gabrielqs\Boleto\Model\Remittance\File\OrderRepository;
use \Gabrielqs\Boleto\Api\Data\RemittanceFileOrderInterface;
use \Gabrielqs\Boleto\Controller\Adminhtml\RegistryConstants;
use \Gabrielqs\Boleto\Block\Adminhtml\Remittance\View\Tab\Orders\IdRenderer;

class Orders extends Extended
{
    /**
     * Core Registry
     * @var Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * Remittance File Orders Repository
     * @var OrderRepository|null
     */
    protected $_remittanceFileOrdersRepository = null;

    /**
     * Search Criteria Builder
     * @var SearchCriteriaBuilder|null
     */
    protected $_searchCriteriaBuilder = null;

    /**
     * Constructor
     * @param Context $context
     * @param BackendHelper $backendHelper
     * @param OrderRepository $ordersRepository
     * @param Registry $coreRegistry
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     */
    public function __construct(
        Context $context,
        BackendHelper $backendHelper,
        OrderRepository $ordersRepository,
        Registry $coreRegistry,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->_remittanceFileOrdersRepository = $ordersRepository;
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
            ->addFilter(RemittanceFileOrderInterface::REMITTANCE_FILE_ID, $this->_getRemittanceFileId(), 'eq')
            ->create();
        $collection = $this->_remittanceFileOrdersRepository->getList($searchCriteria);
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
            RemittanceFileOrderInterface::REMITTANCE_FILE_ORDER_ID,
            [
                'header' => __('Id'),
                'sortable' => true,
                'index' => RemittanceFileOrderInterface::REMITTANCE_FILE_ORDER_ID,
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            RemittanceFileOrderInterface::ORDER_ID,
            [
                'header' => __('Order Id'),
                'index' => RemittanceFileOrderInterface::ORDER_ID,
                'renderer' => IdRenderer::class
            ]
        );

        return parent::_prepareColumns();
    }


    /**
     * Retrieve the Url for a specified sales order row.
     *
     * @param \Magento\Sales\Model\Order|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('sales/order/view', ['order_id' => $row->getData(RemittanceFileOrderInterface::ORDER_ID)]);
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('boleto/remittance/view', ['_current' => true]);
    }

}