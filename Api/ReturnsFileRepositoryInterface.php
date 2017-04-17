<?php

namespace Gabrielqs\Boleto\Api;

use \Magento\Framework\Api\SearchCriteriaInterface;
use \Magento\Framework\Exception\LocalizedException;
use \Magento\Framework\Exception\NoSuchEntityException;
use \Gabrielqs\Boleto\Api\Data\ReturnsFileInterface;
use \Gabrielqs\Boleto\Api\Data\ReturnsFileSearchResultsInterface;

/**
 * Returns File CRUD interface.
 * @api
 */
interface ReturnsFileRepositoryInterface
{
    /**
     * Save Returns File.
     *
     * @param ReturnsFileInterface $file
     * @return ReturnsFileInterface
     * @throws LocalizedException
     */
    public function save(ReturnsFileInterface $file);

    /**
     * Retrieve Returns File.
     *
     * @param int $returnsFileId
     * @return ReturnsFileInterface
     * @throws LocalizedException
     */
    public function getById($returnsFileId);

    /**
     * Retrieve Returns Files matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return ReturnsFileSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Returns File.
     *
     * @param ReturnsFileInterface $file
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(ReturnsFileInterface $file);

    /**
     * Delete Returns File by ID.
     *
     * @param int $returnsFileId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($returnsFileId);
}
