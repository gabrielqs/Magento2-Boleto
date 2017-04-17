<?php
namespace Gabrielqs\Boleto\Model\ResourceModel\Remittance\File\Order;

use \Gabrielqs\Boleto\Api\Data\RemittanceFileOrderSearchResultsInterface;
use \Gabrielqs\Boleto\Model\ResourceModel\Collection\AbstractCollection;

class Collection extends AbstractCollection implements RemittanceFileOrderSearchResultsInterface
{
    /**
     * Remittance File Order Collection Constructor
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Gabrielqs\Boleto\Model\Remittance\File\Order',
            'Gabrielqs\Boleto\Model\ResourceModel\Remittance\File\Order');
    }
}
