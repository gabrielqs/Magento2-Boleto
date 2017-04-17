<?php

namespace Gabrielqs\Boleto\Api\Data;

use \Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for boleto returns File Event search results.
 * @api
 */
interface ReturnsFileEventSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get ReturnsFileEvent list.
     *
     * @return ReturnsFileEventInterface[]
     */
    public function getItems();

    /**
     * Set ReturnsFileEvent list.
     *
     * @param ReturnsFileEventInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
