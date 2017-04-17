<?php
namespace Gabrielqs\Boleto\Model\ResourceModel\Returns\File;

use \Gabrielqs\Boleto\Api\Data\ReturnsFileSearchResultsInterface;
use \Gabrielqs\Boleto\Model\ResourceModel\Collection\AbstractCollection;

class Collection extends AbstractCollection implements ReturnsFileSearchResultsInterface
{
    /**
     * Returns File Collection Constructor
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Gabrielqs\Boleto\Model\Returns\File', 'Gabrielqs\Boleto\Model\ResourceModel\Returns\File');
    }
}
