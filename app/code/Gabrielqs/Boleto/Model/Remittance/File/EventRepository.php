<?php
namespace Gabrielqs\Boleto\Model\Remittance\File;

use \Gabrielqs\Boleto\Api\RemittanceFileEventRepositoryInterface;
use \Magento\Framework\Api\DataObjectHelper;
use \Magento\Framework\Api\SortOrder;
use \Magento\Framework\Exception\CouldNotDeleteException;
use \Magento\Framework\Exception\CouldNotSaveException;
use \Magento\Framework\Exception\NoSuchEntityException;
use \Magento\Framework\Reflection\DataObjectProcessor;
use \Magento\Framework\Api\SearchCriteriaInterface;
use \Magento\Framework\Api\Search\FilterGroup;
use \Gabrielqs\Boleto\Model\Remittance\File\Event as RemittanceFileEvent;
use \Gabrielqs\Boleto\Model\ResourceModel\Remittance\File\Event as RemittanceFileEventResource;
use \Gabrielqs\Boleto\Model\Remittance\File\EventFactory as RemittanceFileEventFactory;
use \Gabrielqs\Boleto\Model\ResourceModel\Remittance\File\Event\CollectionFactory as RemittanceFileEventCollectionFactory;
use \Gabrielqs\Boleto\Api\Data\RemittanceFileEventInterfaceFactory;
use \Gabrielqs\Boleto\Api\Data\RemittanceFileEventSearchResultsInterface;
use \Gabrielqs\Boleto\Api\Data\RemittanceFileEventSearchResultsInterfaceFactory;
use \Gabrielqs\Boleto\Api\Data\RemittanceFileEventInterface;

/**
 * Class EventRepository
 * @package Gabrielqs\Boleto\Model\Remittance
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EventRepository implements RemittanceFileEventRepositoryInterface
{
    /**
     * Remittance File Event Resource
     * @var RemittanceFileEventResource
     */
    protected $resource;

    /**
     * File Factory
     * @var RemittanceFileEventFactory
     */
    protected $remittanceFileEventFactory;

    /**
     * File Collection Factory
     * @var RemittanceFileEventCollectionFactory
     */
    protected $remittanceFileEventCollectionFactory;

    /**
     * Remittance File Event Search Results Interface
     * @var RemittanceFileEventSearchResultsInterface
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
     * Remittance File Event Interface Factory
     * @var RemittanceFileEventInterfaceFactory
     */
    protected $remittanceFileEventInterfaceFactory;

    /**
     * RemittanceFileEvent Repository
     * @param RemittanceFileEventResource $resource
     * @param RemittanceFileEventFactory $remittanceFileEventFactory
     * @param RemittanceFileEventInterfaceFactory $remittanceFileEventInterfaceFactory
     * @param RemittanceFileEventCollectionFactory $remittanceFileEventCollectionFactory
     * @param RemittanceFileEventSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        RemittanceFileEventResource $resource,
        RemittanceFileEventFactory $remittanceFileEventFactory,
        RemittanceFileEventInterfaceFactory $remittanceFileEventInterfaceFactory,
        RemittanceFileEventCollectionFactory $remittanceFileEventCollectionFactory,
        RemittanceFileEventSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->resource = $resource;
        $this->remittanceFileEventFactory = $remittanceFileEventFactory;
        $this->remittanceFileEventCollectionFactory = $remittanceFileEventCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->remittanceFileEventInterfaceFactory = $remittanceFileEventInterfaceFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     * @param FilterGroup $filterGroup
     * @param RemittanceFileEventSearchResultsInterface $searchResult
     * @return void
     */
    protected function addFilterGroupToCollection(
        FilterGroup $filterGroup,
        RemittanceFileEventSearchResultsInterface $searchResult
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
     * Delete RemittanceFileEvent
     * @param RemittanceFileEventInterface $remittanceFileEvent
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(RemittanceFileEventInterface $remittanceFileEvent)
    {
        try {
            $this->resource->delete($remittanceFileEvent);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete RemittanceFileEvent by given RemittanceFileEvent Identity
     * @param string $remittanceFileEventId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($remittanceFileEventId)
    {
        return $this->delete($this->getById($remittanceFileEventId));
    }

    /**
     * Load Remittance File Event data by given RemittanceFileEvent Identity
     * @param string $remittanceFileEventId
     * @return RemittanceFileEvent
     * @throws NoSuchEntityException
     */
    public function getById($remittanceFileEventId)
    {
        $remittanceFileEvent = $this->remittanceFileEventFactory->create();
        $this->resource->load($remittanceFileEvent, $remittanceFileEventId);
        if (!$remittanceFileEvent->getId()) {
            throw new NoSuchEntityException(__('Remittance File Event with id "%1" does not exist.', $remittanceFileEventId));
        }
        return $remittanceFileEvent;
    }

    /**
     * Load RemittanceFileEvent data collection by given search criteria
     * @param SearchCriteriaInterface $searchCriteria
     * @return RemittanceFileEventSearchResultsInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var RemittanceFileEventSearchResultsInterface $searchResult */
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
     * Save Remittance File Event data
     * @param RemittanceFileEventInterface $remittanceFileEvent
     * @return RemittanceFileEvent
     * @throws CouldNotSaveException
     */
    public function save(RemittanceFileEventInterface $remittanceFileEvent)
    {
        try {
            $this->resource->save($remittanceFileEvent);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $remittanceFileEvent;
    }
}
