<?php
namespace Gabrielqs\Boleto\Model\ResourceModel\Remittance\File;

use \Gabrielqs\Boleto\Api\Data\RemittanceFileSearchResultsInterface;
use \Gabrielqs\Boleto\Model\ResourceModel\Collection\AbstractCollection;

class Collection extends AbstractCollection implements RemittanceFileSearchResultsInterface
{
    /**
     * Remittance File Collection Constructor
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Gabrielqs\Boleto\Model\Remittance\File', 'Gabrielqs\Boleto\Model\ResourceModel\Remittance\File');
    }
}
