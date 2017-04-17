<?php

namespace Gabrielqs\Boleto\Api;

use \Magento\Framework\Api\SearchCriteriaInterface;
use \Magento\Framework\Exception\LocalizedException;
use \Magento\Framework\Exception\NoSuchEntityException;
use \Gabrielqs\Boleto\Api\Data\RemittanceFileOrderInterface;
use \Gabrielqs\Boleto\Api\Data\RemittanceFileOrderSearchResultsInterface;

/**
 * Remittance File Order CRUD interface.
 * @api
 */
interface RemittanceFileOrderRepositoryInterface
{
    /**
     * Save Remittance File Order.
     *
     * @param RemittanceFileOrderInterface $file
     * @return RemittanceFileOrderInterface
     * @throws LocalizedException
     */
    public function save(RemittanceFileOrderInterface $file);

    /**
     * Retrieve Remittance File Order.
     *
     * @param int $remittanceFileOrderId
     * @return RemittanceFileOrderInterface
     * @throws LocalizedException
     */
    public function getById($remittanceFileOrderId);

    /**
     * Retrieve Remittance File Orders matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return RemittanceFileOrderSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Remittance File Order.
     *
     * @param RemittanceFileOrderInterface $file
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(RemittanceFileOrderInterface $file);

    /**
     * Delete Remittance File Order by ID.
     *
     * @param int $remittanceFileOrderId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($remittanceFileOrderId);
}
