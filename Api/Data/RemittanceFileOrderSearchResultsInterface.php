<?php

namespace Gabrielqs\Boleto\Api\Data;

use \Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for boleto remittance File Order search results.
 * @api
 */
interface RemittanceFileOrderSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get RemittanceFileOrder list.
     *
     * @return RemittanceFileOrderInterface[]
     */
    public function getItems();

    /**
     * Set RemittanceFileOrder list.
     *
     * @param RemittanceFileOrderInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
