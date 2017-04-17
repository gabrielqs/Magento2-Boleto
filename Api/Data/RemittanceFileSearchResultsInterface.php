<?php

namespace Gabrielqs\Boleto\Api\Data;

use \Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for boleto remittance file search results.
 * @api
 */
interface RemittanceFileSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get RemittanceFile list.
     *
     * @return RemittanceFileInterface[]
     */
    public function getItems();

    /**
     * Set RemittanceFile list.
     *
     * @param RemittanceFileInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
