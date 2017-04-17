<?php
namespace Gabrielqs\Boleto\Model\ResourceModel\Returns\File\Event;

use \Gabrielqs\Boleto\Api\Data\ReturnsFileEventSearchResultsInterface;
use \Gabrielqs\Boleto\Model\ResourceModel\Collection\AbstractCollection;

class Collection extends AbstractCollection implements ReturnsFileEventSearchResultsInterface
{
    /**
     * Returns File Event Collection Constructor
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Gabrielqs\Boleto\Model\Returns\File\Event',
            'Gabrielqs\Boleto\Model\ResourceModel\Returns\File\Event');
    }
}
