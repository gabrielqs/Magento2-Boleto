<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Gabrielqs\Boleto\Model\ResourceModel\Collection;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection as FrameworkAbstractCollection;
use \Magento\Framework\DB\Select;
use \Magento\Framework\Api\SearchCriteriaInterface;
use \Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Gabrielqs Boleto abstract collection
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractCollection extends FrameworkAbstractCollection
{
    /**
     * Count Select
     * @var Select
     */
    protected $_countSelect;

    /**
     * Search Criteria
     * @var SearchCriteriaInterface
     */
    protected $searchCriteria;

    /**
     * Set select count sql
     * @param Select $countSelect
     * @return $this
     */
    public function setSelectCountSql(Select $countSelect)
    {
        $this->_countSelect = $countSelect;
        return $this;
    }

    /**
     * Get select count sql
     * @return Select
     */
    public function getSelectCountSql()
    {
        if (!$this->_countSelect instanceof Select) {
            $this->setSelectCountSql(parent::getSelectCountSql());
        }
        return $this->_countSelect;
    }

    /**
     * Set collection page start and records to show
     * Backward compatibility with EAV collection
     * @param int $pageNum
     * @param int $pageSize
     * @return $this
     */
    public function setPage($pageNum, $pageSize)
    {
        $this->setCurPage($pageNum)->setPageSize($pageSize);
        return $this;
    }

    /**
     * Create all ids retrieving select with limitation
     * Backward compatibility with EAV collection
     * @param int $limit
     * @param int $offset
     * @return Select
     */
    protected function _getAllIdsSelect($limit = null, $offset = null)
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(Select::ORDER);
        $idsSelect->reset(Select::LIMIT_COUNT);
        $idsSelect->reset(Select::LIMIT_OFFSET);
        $idsSelect->reset(Select::COLUMNS);
        $idsSelect->columns($this->getResource()->getIdFieldName(), 'main_table');
        $idsSelect->limit($limit, $offset);
        return $idsSelect;
    }

    /**
     * Retrieve all ids for collection
     * Backward compatibility with EAV collection
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAllIds($limit = null, $offset = null)
    {
        return $this->getConnection()->fetchCol($this->_getAllIdsSelect($limit, $offset), $this->_bindParams);
    }

    /**
     * Get search criteria.
     * @return SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return $this->searchCriteria;
    }

    /**
     * Set search criteria.
     * @param SearchCriteriaInterface $searchCriteria
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria = null)
    {
        $this->searchCriteria = $searchCriteria;
        return $this;
    }

    /**
     * Get total count.
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     * @param int $totalCount
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     * @param ExtensibleDataInterface[] $items
     * @return $this
     */
    public function setItems(array $items = null)
    {
        if (!$items) {
            return $this;
        }
        foreach ($items as $item) {
            $this->addItem($item);
        }
        return $this;
    }
}
