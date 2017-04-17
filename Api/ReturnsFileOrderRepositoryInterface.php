<?php

namespace Gabrielqs\Boleto\Api;

use \Magento\Framework\Api\SearchCriteriaInterface;
use \Magento\Framework\Exception\LocalizedException;
use \Magento\Framework\Exception\NoSuchEntityException;
use \Gabrielqs\Boleto\Api\Data\ReturnsFileOrderInterface;
use \Gabrielqs\Boleto\Api\Data\ReturnsFileOrderSearchResultsInterface;

/**
 * Returns File Order CRUD interface.
 * @api
 */
interface ReturnsFileOrderRepositoryInterface
{
    /**
     * Save Returns File Order.
     *
     * @param ReturnsFileOrderInterface $file
     * @return ReturnsFileOrderInterface
     * @throws LocalizedException
     */
    public function save(ReturnsFileOrderInterface $file);

    /**
     * Retrieve Returns File Order.
     *
     * @param int $returnsFileOrderId
     * @return ReturnsFileOrderInterface
     * @throws LocalizedException
     */
    public function getById($returnsFileOrderId);

    /**
     * Retrieve Returns File Orders matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return ReturnsFileOrderSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Returns File Order.
     *
     * @param ReturnsFileOrderInterface $file
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(ReturnsFileOrderInterface $file);

    /**
     * Delete Returns File Order by ID.
     *
     * @param int $returnsFileOrderId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($returnsFileOrderId);
}
