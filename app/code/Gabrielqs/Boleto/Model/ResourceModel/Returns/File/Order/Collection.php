<?php
namespace Gabrielqs\Boleto\Model\ResourceModel\Returns\File\Order;

use \Gabrielqs\Boleto\Api\Data\ReturnsFileOrderSearchResultsInterface;
use \Gabrielqs\Boleto\Model\ResourceModel\Collection\AbstractCollection;

class Collection extends AbstractCollection implements ReturnsFileOrderSearchResultsInterface
{
    /**
     * Returns File Order Collection Constructor
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Gabrielqs\Boleto\Model\Returns\File\Order',
            'Gabrielqs\Boleto\Model\ResourceModel\Returns\File\Order');
    }
}
