<?php
namespace Gabrielqs\Boleto\Model\Returns\File;

use \Gabrielqs\Boleto\Api\ReturnsFileEventRepositoryInterface;
use \Magento\Framework\Api\DataObjectHelper;
use \Magento\Framework\Api\SortOrder;
use \Magento\Framework\Exception\CouldNotDeleteException;
use \Magento\Framework\Exception\CouldNotSaveException;
use \Magento\Framework\Exception\NoSuchEntityException;
use \Magento\Framework\Reflection\DataObjectProcessor;
use \Magento\Framework\Api\SearchCriteriaInterface;
use \Magento\Framework\Api\Search\FilterGroup;
use \Gabrielqs\Boleto\Model\Returns\File\Event as ReturnsFileEvent;
use \Gabrielqs\Boleto\Model\ResourceModel\Returns\File\Event as ReturnsFileEventResource;
use \Gabrielqs\Boleto\Model\Returns\File\EventFactory as ReturnsFileEventFactory;
use \Gabrielqs\Boleto\Model\ResourceModel\Returns\File\Event\CollectionFactory as ReturnsFileEventCollectionFactory;
use \Gabrielqs\Boleto\Api\Data\ReturnsFileEventInterfaceFactory;
use \Gabrielqs\Boleto\Api\Data\ReturnsFileEventSearchResultsInterface;
use \Gabrielqs\Boleto\Api\Data\ReturnsFileEventSearchResultsInterfaceFactory;
use \Gabrielqs\Boleto\Api\Data\ReturnsFileEventInterface;

/**
 * Class EventRepository
 * @package Gabrielqs\Boleto\Model\Returns
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EventRepository implements ReturnsFileEventRepositoryInterface
{
    /**
     * Returns File Event Resource
     * @var ReturnsFileEventResource
     */
    protected $resource;

    /**
     * File Factory
     * @var ReturnsFileEventFactory
     */
    protected $returnsFileEventFactory;

    /**
     * File Collection Factory
     * @var ReturnsFileEventCollectionFactory
     */
    protected $returnsFileEventCollectionFactory;

    /**
     * Returns File Event Search Results Interface
     * @var ReturnsFileEventSearchResultsInterface
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
     * Returns File Event Interface Factory
     * @var ReturnsFileEventInterfaceFactory
     */
    protected $returnsFileEventInterfaceFactory;

    /**
     * ReturnsFileEvent Repository
     * @param ReturnsFileEventResource $resource
     * @param ReturnsFileEventFactory $returnsFileEventFactory
     * @param ReturnsFileEventInterfaceFactory $returnsFileEventInterfaceFactory
     * @param ReturnsFileEventCollectionFactory $returnsFileEventCollectionFactory
     * @param ReturnsFileEventSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        ReturnsFileEventResource $resource,
        ReturnsFileEventFactory $returnsFileEventFactory,
        ReturnsFileEventInterfaceFactory $returnsFileEventInterfaceFactory,
        ReturnsFileEventCollectionFactory $returnsFileEventCollectionFactory,
        ReturnsFileEventSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->resource = $resource;
        $this->returnsFileEventFactory = $returnsFileEventFactory;
        $this->returnsFileEventCollectionFactory = $returnsFileEventCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->returnsFileEventInterfaceFactory = $returnsFileEventInterfaceFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     * @param FilterGroup $filterGroup
     * @param ReturnsFileEventSearchResultsInterface $searchResult
     * @return void
     */
    protected function addFilterGroupToCollection(
        FilterGroup $filterGroup,
        ReturnsFileEventSearchResultsInterface $searchResult
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
     * Delete ReturnsFileEvent
     * @param ReturnsFileEventInterface $returnsFileEvent
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ReturnsFileEventInterface $returnsFileEvent)
    {
        try {
            $this->resource->delete($returnsFileEvent);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete ReturnsFileEvent by given ReturnsFileEvent Identity
     * @param string $returnsFileEventId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($returnsFileEventId)
    {
        return $this->delete($this->getById($returnsFileEventId));
    }

    /**
     * Load Returns File Event data by given ReturnsFileEvent Identity
     * @param string $returnsFileEventId
     * @return ReturnsFileEvent
     * @throws NoSuchEntityException
     */
    public function getById($returnsFileEventId)
    {
        $returnsFileEvent = $this->returnsFileEventFactory->create();
        $this->resource->load($returnsFileEvent, $returnsFileEventId);
        if (!$returnsFileEvent->getId()) {
            throw new NoSuchEntityException(__('Returns File Event with id "%1" does not exist.', $returnsFileEventId));
        }
        return $returnsFileEvent;
    }

    /**
     * Load ReturnsFileEvent data collection by given search criteria
     * @param SearchCriteriaInterface $searchCriteria
     * @return ReturnsFileEventSearchResultsInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var ReturnsFileEventSearchResultsInterface $searchResult */
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
     * Save Returns File Event data
     * @param ReturnsFileEventInterface $returnsFileEvent
     * @return ReturnsFileEvent
     * @throws CouldNotSaveException
     */
    public function save(ReturnsFileEventInterface $returnsFileEvent)
    {
        try {
            $this->resource->save($returnsFileEvent);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $returnsFileEvent;
    }
}
