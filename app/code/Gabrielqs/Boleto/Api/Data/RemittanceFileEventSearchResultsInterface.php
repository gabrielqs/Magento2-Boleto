<?php

namespace Gabrielqs\Boleto\Api\Data;

use \Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for boleto remittance File Event search results.
 * @api
 */
interface RemittanceFileEventSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get RemittanceFileEvent list.
     *
     * @return RemittanceFileEventInterface[]
     */
    public function getItems();

    /**
     * Set RemittanceFileEvent list.
     *
     * @param RemittanceFileEventInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
