<?php
namespace Gabrielqs\Boleto\Model\Returns;

use \Gabrielqs\Boleto\Api\ReturnsFileRepositoryInterface;
use \Magento\Framework\Api\DataObjectHelper;
use \Magento\Framework\Api\SortOrder;
use \Magento\Framework\Exception\CouldNotDeleteException;
use \Magento\Framework\Exception\CouldNotSaveException;
use \Magento\Framework\Exception\NoSuchEntityException;
use \Magento\Framework\Reflection\DataObjectProcessor;
use \Magento\Framework\Api\SearchCriteriaInterface;
use \Magento\Framework\Api\Search\FilterGroup;
use \Gabrielqs\Boleto\Model\Returns\File as ReturnsFile;
use \Gabrielqs\Boleto\Model\ResourceModel\Returns\File as ReturnsFileResource;
use \Gabrielqs\Boleto\Model\Returns\FileFactory as ReturnsFileFactory;
use \Gabrielqs\Boleto\Model\ResourceModel\Returns\File\CollectionFactory as ReturnsFileCollectionFactory;
use \Gabrielqs\Boleto\Api\Data\ReturnsFileInterfaceFactory;
use \Gabrielqs\Boleto\Api\Data\ReturnsFileSearchResultsInterface;
use \Gabrielqs\Boleto\Api\Data\ReturnsFileSearchResultsInterfaceFactory;
use \Gabrielqs\Boleto\Api\Data\ReturnsFileInterface;

/**
 * Class FileRepository
 * @package Gabrielqs\Boleto\Model\Returns
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FileRepository implements ReturnsFileRepositoryInterface
{
    /**
     * Returns File Resource
     * @var ReturnsFileResource
     */
    protected $resource;

    /**
     * File Factory
     * @var ReturnsFileFactory
     */
    protected $returnsFileFactory;

    /**
     * File Collection Factory
     * @var ReturnsFileCollectionFactory
     */
    protected $returnsFileCollectionFactory;

    /**
     * Returns File Search Results Interface
     * @var ReturnsFileSearchResultsInterface
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
     * Returns File Interface Factory
     * @var ReturnsFileInterfaceFactory
     */
    protected $returnsFileInterfaceFactory;

    /**
     * ReturnsFile Repository
     * @param ReturnsFileResource $resource
     * @param ReturnsFileFactory $returnsFileFactory
     * @param ReturnsFileInterfaceFactory $returnsFileInterfaceFactory
     * @param ReturnsFileCollectionFactory $returnsFileCollectionFactory
     * @param ReturnsFileSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        ReturnsFileResource $resource,
        ReturnsFileFactory $returnsFileFactory,
        ReturnsFileInterfaceFactory $returnsFileInterfaceFactory,
        ReturnsFileCollectionFactory $returnsFileCollectionFactory,
        ReturnsFileSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->resource = $resource;
        $this->returnsFileFactory = $returnsFileFactory;
        $this->returnsFileCollectionFactory = $returnsFileCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->returnsFileInterfaceFactory = $returnsFileInterfaceFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     * @param FilterGroup $filterGroup
     * @param ReturnsFileSearchResultsInterface $searchResult
     * @return void
     */
    protected function addFilterGroupToCollection(
        FilterGroup $filterGroup,
        ReturnsFileSearchResultsInterface $searchResult
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
     * Delete ReturnsFile
     * @param ReturnsFileInterface $returnsFile
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ReturnsFileInterface $returnsFile)
    {
        try {
            $this->resource->delete($returnsFile);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete ReturnsFile by given ReturnsFile Identity
     * @param string $returnsFileId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($returnsFileId)
    {
        return $this->delete($this->getById($returnsFileId));
    }

    /**
     * Load Returns File data by given ReturnsFile Identity
     * @param string $returnsFileId
     * @return ReturnsFile
     * @throws NoSuchEntityException
     */
    public function getById($returnsFileId)
    {
        $returnsFile = $this->returnsFileFactory->create();
        $this->resource->load($returnsFile, $returnsFileId);
        if (!$returnsFile->getId()) {
            throw new NoSuchEntityException(__('Returns File with id "%1" does not exist.', $returnsFileId));
        }
        return $returnsFile;
    }

    /**
     * Load ReturnsFile data collection by given search criteria
     * @param SearchCriteriaInterface $searchCriteria
     * @return ReturnsFileSearchResultsInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var ReturnsFileSearchResultsInterface $searchResult */
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
     * Save Returns File data
     * @param ReturnsFileInterface $returnsFile
     * @return ReturnsFile
     * @throws CouldNotSaveException
     */
    public function save(ReturnsFileInterface $returnsFile)
    {
        try {
            $this->resource->save($returnsFile);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $returnsFile;
    }
}
