<?php

namespace Gabrielqs\Boleto\Model\ResourceModel\Remittance\File;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Event extends AbstractDb
{
    /**
     * Remittance File Event Resource Constructor
     * @return void
     */
    protected function _construct()
    {
        $this->_init('boleto_remittance_file_event', 'remittance_file_event_id');
    }
}
