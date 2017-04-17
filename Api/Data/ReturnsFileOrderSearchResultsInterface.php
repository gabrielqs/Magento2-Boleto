<?php

namespace Gabrielqs\Boleto\Api\Data;

use \Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for boleto returns File Order search results.
 * @api
 */
interface ReturnsFileOrderSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get ReturnsFileOrder list.
     *
     * @return ReturnsFileOrderInterface[]
     */
    public function getItems();

    /**
     * Set ReturnsFileOrder list.
     *
     * @param ReturnsFileOrderInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
