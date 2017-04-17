<?php
namespace Gabrielqs\Boleto\Model\Remittance;

use \Gabrielqs\Boleto\Api\RemittanceFileRepositoryInterface;
use \Magento\Framework\Api\DataObjectHelper;
use \Magento\Framework\Api\SortOrder;
use \Magento\Framework\Exception\CouldNotDeleteException;
use \Magento\Framework\Exception\CouldNotSaveException;
use \Magento\Framework\Exception\NoSuchEntityException;
use \Magento\Framework\Reflection\DataObjectProcessor;
use \Magento\Framework\Api\SearchCriteriaInterface;
use \Magento\Framework\Api\Search\FilterGroup;
use \Gabrielqs\Boleto\Model\Remittance\File as RemittanceFile;
use \Gabrielqs\Boleto\Model\ResourceModel\Remittance\File as RemittanceFileResource;
use \Gabrielqs\Boleto\Model\Remittance\FileFactory as RemittanceFileFactory;
use \Gabrielqs\Boleto\Model\ResourceModel\Remittance\File\CollectionFactory as RemittanceFileCollectionFactory;
use \Gabrielqs\Boleto\Api\Data\RemittanceFileInterfaceFactory;
use \Gabrielqs\Boleto\Api\Data\RemittanceFileSearchResultsInterface;
use \Gabrielqs\Boleto\Api\Data\RemittanceFileSearchResultsInterfaceFactory;
use \Gabrielqs\Boleto\Api\Data\RemittanceFileInterface;

/**
 * Class FileRepository
 * @package Gabrielqs\Boleto\Model\Remittance
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FileRepository implements RemittanceFileRepositoryInterface
{
    /**
     * Remittance File Resource
     * @var RemittanceFileResource
     */
    protected $resource;

    /**
     * File Factory
     * @var RemittanceFileFactory
     */
    protected $remittanceFileFactory;

    /**
     * File Collection Factory
     * @var RemittanceFileCollectionFactory
     */
    protected $remittanceFileCollectionFactory;

    /**
     * Remittance File Search Results Interface
     * @var RemittanceFileSearchResultsInterface
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
     * Remittance File Interface Factory
     * @var RemittanceFileInterfaceFactory
     */
    protected $remittanceFileInterfaceFactory;

    /**
     * RemittanceFile Repository
     * @param RemittanceFileResource $resource
     * @param RemittanceFileFactory $remittanceFileFactory
     * @param RemittanceFileInterfaceFactory $remittanceFileInterfaceFactory
     * @param RemittanceFileCollectionFactory $remittanceFileCollectionFactory
     * @param RemittanceFileSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        RemittanceFileResource $resource,
        RemittanceFileFactory $remittanceFileFactory,
        RemittanceFileInterfaceFactory $remittanceFileInterfaceFactory,
        RemittanceFileCollectionFactory $remittanceFileCollectionFactory,
        RemittanceFileSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->resource = $resource;
        $this->remittanceFileFactory = $remittanceFileFactory;
        $this->remittanceFileCollectionFactory = $remittanceFileCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->remittanceFileInterfaceFactory = $remittanceFileInterfaceFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     * @param FilterGroup $filterGroup
     * @param RemittanceFileSearchResultsInterface $searchResult
     * @return void
     */
    protected function addFilterGroupToCollection(
        FilterGroup $filterGroup,
        RemittanceFileSearchResultsInterface $searchResult
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
     * Delete RemittanceFile
     * @param RemittanceFileInterface $remittanceFile
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(RemittanceFileInterface $remittanceFile)
    {
        try {
            $this->resource->delete($remittanceFile);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete RemittanceFile by given RemittanceFile Identity
     * @param string $remittanceFileId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($remittanceFileId)
    {
        return $this->delete($this->getById($remittanceFileId));
    }

    /**
     * Load Remittance File data by given RemittanceFile Identity
     * @param string $remittanceFileId
     * @return RemittanceFile
     * @throws NoSuchEntityException
     */
    public function getById($remittanceFileId)
    {
        $remittanceFile = $this->remittanceFileFactory->create();
        $this->resource->load($remittanceFile, $remittanceFileId);
        if (!$remittanceFile->getId()) {
            throw new NoSuchEntityException(__('Remittance File with id "%1" does not exist.', $remittanceFileId));
        }
        return $remittanceFile;
    }

    /**
     * Load RemittanceFile data collection by given search criteria
     * @param SearchCriteriaInterface $searchCriteria
     * @return RemittanceFileSearchResultsInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var RemittanceFileSearchResultsInterface $searchResult */
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
     * Save Remittance File data
     * @param RemittanceFileInterface $remittanceFile
     * @return RemittanceFile
     * @throws CouldNotSaveException
     */
    public function save(RemittanceFileInterface $remittanceFile)
    {
        try {
            $this->resource->save($remittanceFile);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $remittanceFile;
    }
}
