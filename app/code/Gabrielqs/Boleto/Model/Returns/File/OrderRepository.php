<?php
namespace Gabrielqs\Boleto\Model\Returns\File;

use \Gabrielqs\Boleto\Api\ReturnsFileOrderRepositoryInterface;
use \Magento\Framework\Api\DataObjectHelper;
use \Magento\Framework\Api\SortOrder;
use \Magento\Framework\Exception\CouldNotDeleteException;
use \Magento\Framework\Exception\CouldNotSaveException;
use \Magento\Framework\Exception\NoSuchEntityException;
use \Magento\Framework\Reflection\DataObjectProcessor;
use \Magento\Framework\Api\SearchCriteriaInterface;
use \Magento\Framework\Api\Search\FilterGroup;
use \Gabrielqs\Boleto\Model\Returns\File\Order as ReturnsFileOrder;
use \Gabrielqs\Boleto\Model\ResourceModel\Returns\File\Order as ReturnsFileOrderResource;
use \Gabrielqs\Boleto\Model\Returns\File\OrderFactory as ReturnsFileOrderFactory;
use \Gabrielqs\Boleto\Model\ResourceModel\Returns\File\Order\CollectionFactory as ReturnsFileOrderCollectionFactory;
use \Gabrielqs\Boleto\Api\Data\ReturnsFileOrderInterfaceFactory;
use \Gabrielqs\Boleto\Api\Data\ReturnsFileOrderSearchResultsInterface;
use \Gabrielqs\Boleto\Api\Data\ReturnsFileOrderSearchResultsInterfaceFactory;
use \Gabrielqs\Boleto\Api\Data\ReturnsFileOrderInterface;

/**
 * Class OrderRepository
 * @package Gabrielqs\Boleto\Model\Returns
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderRepository implements ReturnsFileOrderRepositoryInterface
{
    /**
     * Returns File Order Resource
     * @var ReturnsFileOrderResource
     */
    protected $resource;

    /**
     * File Factory
     * @var ReturnsFileOrderFactory
     */
    protected $returnsFileOrderFactory;

    /**
     * File Collection Factory
     * @var ReturnsFileOrderCollectionFactory
     */
    protected $returnsFileOrderCollectionFactory;

    /**
     * Returns File Order Search Results Interface
     * @var ReturnsFileOrderSearchResultsInterface
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
     * Returns File Order Interface Factory
     * @var ReturnsFileOrderInterfaceFactory
     */
    protected $returnsFileOrderInterfaceFactory;

    /**
     * ReturnsFileOrder Repository
     * @param ReturnsFileOrderResource $resource
     * @param ReturnsFileOrderFactory $returnsFileOrderFactory
     * @param ReturnsFileOrderInterfaceFactory $returnsFileOrderInterfaceFactory
     * @param ReturnsFileOrderCollectionFactory $returnsFileOrderCollectionFactory
     * @param ReturnsFileOrderSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        ReturnsFileOrderResource $resource,
        ReturnsFileOrderFactory $returnsFileOrderFactory,
        ReturnsFileOrderInterfaceFactory $returnsFileOrderInterfaceFactory,
        ReturnsFileOrderCollectionFactory $returnsFileOrderCollectionFactory,
        ReturnsFileOrderSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->resource = $resource;
        $this->returnsFileOrderFactory = $returnsFileOrderFactory;
        $this->returnsFileOrderCollectionFactory = $returnsFileOrderCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->returnsFileOrderInterfaceFactory = $returnsFileOrderInterfaceFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     * @param FilterGroup $filterGroup
     * @param ReturnsFileOrderSearchResultsInterface $searchResult
     * @return void
     */
    protected function addFilterGroupToCollection(
        FilterGroup $filterGroup,
        ReturnsFileOrderSearchResultsInterface $searchResult
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
     * Delete ReturnsFileOrder
     * @param ReturnsFileOrderInterface $returnsFileOrder
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ReturnsFileOrderInterface $returnsFileOrder)
    {
        try {
            $this->resource->delete($returnsFileOrder);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete ReturnsFileOrder by given ReturnsFileOrder Identity
     * @param string $returnsFileOrderId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($returnsFileOrderId)
    {
        return $this->delete($this->getById($returnsFileOrderId));
    }

    /**
     * Load Returns File Order data by given ReturnsFileOrder Identity
     * @param string $returnsFileOrderId
     * @return ReturnsFileOrder
     * @throws NoSuchEntityException
     */
    public function getById($returnsFileOrderId)
    {
        $returnsFileOrder = $this->returnsFileOrderFactory->create();
        $this->resource->load($returnsFileOrder, $returnsFileOrderId);
        if (!$returnsFileOrder->getId()) {
            throw new NoSuchEntityException(__('Returns File Order with id "%1" does not exist.', $returnsFileOrderId));
        }
        return $returnsFileOrder;
    }

    /**
     * Load ReturnsFileOrder data collection by given search criteria
     * @param SearchCriteriaInterface $searchCriteria
     * @return ReturnsFileOrderSearchResultsInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var ReturnsFileOrderSearchResultsInterface $searchResult */
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
     * Save Returns File Order data
     * @param ReturnsFileOrderInterface $returnsFileOrder
     * @return ReturnsFileOrder
     * @throws CouldNotSaveException
     */
    public function save(ReturnsFileOrderInterface $returnsFileOrder)
    {
        try {
            $this->resource->save($returnsFileOrder);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $returnsFileOrder;
    }
}
