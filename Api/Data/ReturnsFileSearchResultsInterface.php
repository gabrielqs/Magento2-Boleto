<?php

namespace Gabrielqs\Boleto\Api\Data;

use \Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for boleto returns file search results.
 * @api
 */
interface ReturnsFileSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get ReturnsFile list.
     *
     * @return ReturnsFileInterface[]
     */
    public function getItems();

    /**
     * Set ReturnsFile list.
     *
     * @param ReturnsFileInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
