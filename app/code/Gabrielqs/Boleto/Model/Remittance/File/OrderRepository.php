<?php
namespace Gabrielqs\Boleto\Model\Remittance\File;

use \Gabrielqs\Boleto\Api\RemittanceFileOrderRepositoryInterface;
use \Magento\Framework\Api\DataObjectHelper;
use \Magento\Framework\Api\SortOrder;
use \Magento\Framework\Exception\CouldNotDeleteException;
use \Magento\Framework\Exception\CouldNotSaveException;
use \Magento\Framework\Exception\NoSuchEntityException;
use \Magento\Framework\Reflection\DataObjectProcessor;
use \Magento\Framework\Api\SearchCriteriaInterface;
use \Magento\Framework\Api\Search\FilterGroup;
use \Gabrielqs\Boleto\Model\Remittance\File\Order as RemittanceFileOrder;
use \Gabrielqs\Boleto\Model\ResourceModel\Remittance\File\Order as RemittanceFileOrderResource;
use \Gabrielqs\Boleto\Model\Remittance\File\OrderFactory as RemittanceFileOrderFactory;
use \Gabrielqs\Boleto\Model\ResourceModel\Remittance\File\Order\CollectionFactory as RemittanceFileOrderCollectionFactory;
use \Gabrielqs\Boleto\Api\Data\RemittanceFileOrderInterfaceFactory;
use \Gabrielqs\Boleto\Api\Data\RemittanceFileOrderSearchResultsInterface;
use \Gabrielqs\Boleto\Api\Data\RemittanceFileOrderSearchResultsInterfaceFactory;
use \Gabrielqs\Boleto\Api\Data\RemittanceFileOrderInterface;

/**
 * Class OrderRepository
 * @package Gabrielqs\Boleto\Model\Remittance
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderRepository implements RemittanceFileOrderRepositoryInterface
{
    /**
     * Remittance File Order Resource
     * @var RemittanceFileOrderResource
     */
    protected $resource;

    /**
     * File Factory
     * @var RemittanceFileOrderFactory
     */
    protected $remittanceFileOrderFactory;

    /**
     * File Collection Factory
     * @var RemittanceFileOrderCollectionFactory
     */
    protected $remittanceFileOrderCollectionFactory;

    /**
     * Remittance File Order Search Results Interface
     * @var RemittanceFileOrderSearchResultsInterface
     */
    protected $searchResultsFactory;

    /**
     * Data Object Helper
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * Data Object Processor
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * Remittance File Order Interface Factory
     * @var RemittanceFileOrderInterfaceFactory
     */
    protected $remittanceFileOrderInterfaceFactory;

    /**
     * RemittanceFileOrder Repository
     * @param RemittanceFileOrderResource $resource
     * @param RemittanceFileOrderFactory $remittanceFileOrderFactory
     * @param RemittanceFileOrderInterfaceFactory $remittanceFileOrderInterfaceFactory
     * @param RemittanceFileOrderCollectionFactory $remittanceFileOrderCollectionFactory
     * @param RemittanceFileOrderSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        RemittanceFileOrderResource $resource,
        RemittanceFileOrderFactory $remittanceFileOrderFactory,
        RemittanceFileOrderInterfaceFactory $remittanceFileOrderInterfaceFactory,
        RemittanceFileOrderCollectionFactory $remittanceFileOrderCollectionFactory,
        RemittanceFileOrderSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->resource = $resource;
        $this->remittanceFileOrderFactory = $remittanceFileOrderFactory;
        $this->remittanceFileOrderCollectionFactory = $remittanceFileOrderCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->remittanceFileOrderInterfaceFactory = $remittanceFileOrderInterfaceFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     * @param FilterGroup $filterGroup
     * @param RemittanceFileOrderSearchResultsInterface $searchResult
     * @return void
     */
    protected function addFilterGroupToCollection(
        FilterGroup $filterGroup,
        RemittanceFileOrderSearchResultsInterface $searchResult
    ) {
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $conditions[] = [$condition => $filter->getValue()];
            $fields[] = $filter->getField();
        }
        if ($fields) {
            $searchResult->addFieldToFilter($fields, $conditions);
        }
    }

    /**
     * Delete RemittanceFileOrder
     * @param RemittanceFileOrderInterface $remittanceFileOrder
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(RemittanceFileOrderInterface $remittanceFileOrder)
    {
        try {
            $this->resource->delete($remittanceFileOrder);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete RemittanceFileOrder by given RemittanceFileOrder Identity
     * @param string $remittanceFileOrderId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($remittanceFileOrderId)
    {
        return $this->delete($this->getById($remittanceFileOrderId));
    }

    /**
     * Load Remittance File Order data by given RemittanceFileOrder Identity
     * @param string $remittanceFileOrderId
     * @return RemittanceFileOrder
     * @throws NoSuchEntityException
     */
    public function getById($remittanceFileOrderId)
    {
        $remittanceFileOrder = $this->remittanceFileOrderFactory->create();
        $this->resource->load($remittanceFileOrder, $remittanceFileOrderId);
        if (!$remittanceFileOrder->getId()) {
            throw new NoSuchEntityException(__('Remittance File Order with id "%1" does not exist.', $remittanceFileOrderId));
        }
        return $remittanceFileOrder;
    }

    /**
     * Load RemittanceFileOrder data collection by given search criteria
     * @param SearchCriteriaInterface $searchCriteria
     * @return RemittanceFileOrderSearchResultsInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var RemittanceFileOrderSearchResultsInterface $searchResult */
        $searchResult = $this->searchResultsFactory->create();
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $this->addFilterGroupToCollection($filterGroup, $searchResult);
        }

        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders === null) {
            $sortOrders = [];
        }
        /** @var \Magento\Framework\Api\SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $searchResult->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
            );
        }

        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setCurPage($searchCriteria->getCurrentPage());
        $searchResult->setPageSize($searchCriteria->getPageSize());
        return $searchResult;
    }

    /**
     * Save Remittance File Order data
     * @param RemittanceFileOrderInterface $remittanceFileOrder
     * @return RemittanceFileOrder
     * @throws CouldNotSaveException
     */
    public function save(RemittanceFileOrderInterface $remittanceFileOrder)
    {
        try {
            $this->resource->save($remittanceFileOrder);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $remittanceFileOrder;
    }
}
