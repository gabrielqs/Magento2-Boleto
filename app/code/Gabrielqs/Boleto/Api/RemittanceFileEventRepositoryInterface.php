<?php

namespace Gabrielqs\Boleto\Api;

use \Magento\Framework\Api\SearchCriteriaInterface;
use \Magento\Framework\Exception\LocalizedException;
use \Magento\Framework\Exception\NoSuchEntityException;
use \Gabrielqs\Boleto\Api\Data\RemittanceFileEventInterface;
use \Gabrielqs\Boleto\Api\Data\RemittanceFileEventSearchResultsInterface;

/**
 * Remittance File Event CRUD interface.
 * @api
 */
interface RemittanceFileEventRepositoryInterface
{
    /**
     * Save Remittance File Event.
     *
     * @param RemittanceFileEventInterface $file
     * @return RemittanceFileEventInterface
     * @throws LocalizedException
     */
    public function save(RemittanceFileEventInterface $file);

    /**
     * Retrieve Remittance File Event.
     *
     * @param int $remittanceFileEventId
     * @return RemittanceFileEventInterface
     * @throws LocalizedException
     */
    public function getById($remittanceFileEventId);

    /**
     * Retrieve Remittance File Events matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return RemittanceFileEventSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Remittance File Event.
     *
     * @param RemittanceFileEventInterface $file
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(RemittanceFileEventInterface $file);

    /**
     * Delete Remittance File Event by ID.
     *
     * @param int $remittanceFileEventId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($remittanceFileEventId);
}
