<?php

namespace Gabrielqs\Boleto\Api;

use \Magento\Framework\Api\SearchCriteriaInterface;
use \Magento\Framework\Exception\LocalizedException;
use \Magento\Framework\Exception\NoSuchEntityException;
use \Gabrielqs\Boleto\Api\Data\ReturnsFileEventInterface;
use \Gabrielqs\Boleto\Api\Data\ReturnsFileEventSearchResultsInterface;

/**
 * Returns File Event CRUD interface.
 * @api
 */
interface ReturnsFileEventRepositoryInterface
{
    /**
     * Save Returns File Event.
     *
     * @param ReturnsFileEventInterface $file
     * @return ReturnsFileEventInterface
     * @throws LocalizedException
     */
    public function save(ReturnsFileEventInterface $file);

    /**
     * Retrieve Returns File Event.
     *
     * @param int $returnsFileEventId
     * @return ReturnsFileEventInterface
     * @throws LocalizedException
     */
    public function getById($returnsFileEventId);

    /**
     * Retrieve Returns File Events matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return ReturnsFileEventSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Returns File Event.
     *
     * @param ReturnsFileEventInterface $file
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(ReturnsFileEventInterface $file);

    /**
     * Delete Returns File Event by ID.
     *
     * @param int $returnsFileEventId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($returnsFileEventId);
}
