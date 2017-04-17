<?php
namespace Gabrielqs\Boleto\Model\ResourceModel\Remittance\File\Event;

use \Gabrielqs\Boleto\Api\Data\RemittanceFileEventSearchResultsInterface;
use \Gabrielqs\Boleto\Model\ResourceModel\Collection\AbstractCollection;

class Collection extends AbstractCollection implements RemittanceFileEventSearchResultsInterface
{
    /**
     * Remittance File Event Collection Constructor
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Gabrielqs\Boleto\Model\Remittance\File\Event',
            'Gabrielqs\Boleto\Model\ResourceModel\Remittance\File\Event');
    }
}
