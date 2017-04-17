<?php

namespace Gabrielqs\Boleto\Api;

use \Magento\Framework\Api\SearchCriteriaInterface;
use \Magento\Framework\Exception\LocalizedException;
use \Magento\Framework\Exception\NoSuchEntityException;
use \Gabrielqs\Boleto\Api\Data\RemittanceFileInterface;
use \Gabrielqs\Boleto\Api\Data\RemittanceFileSearchResultsInterface;

/**
 * Remittance File CRUD interface.
 * @api
 */
interface RemittanceFileRepositoryInterface
{
    /**
     * Save Remittance File.
     *
     * @param RemittanceFileInterface $file
     * @return RemittanceFileInterface
     * @throws LocalizedException
     */
    public function save(RemittanceFileInterface $file);

    /**
     * Retrieve Remittance File.
     *
     * @param int $remittanceFileId
     * @return RemittanceFileInterface
     * @throws LocalizedException
     */
    public function getById($remittanceFileId);

    /**
     * Retrieve Remittance Files matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return RemittanceFileSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Remittance File.
     *
     * @param RemittanceFileInterface $file
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(RemittanceFileInterface $file);

    /**
     * Delete Remittance File by ID.
     *
     * @param int $remittanceFileId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($remittanceFileId);
}
