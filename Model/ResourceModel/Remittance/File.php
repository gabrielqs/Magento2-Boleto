<?php

namespace Gabrielqs\Boleto\Model\ResourceModel\Remittance;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class File extends AbstractDb
{
    /**
     * Remittance File Abstract Resource Constructor
     * @return void
     */
    protected function _construct()
    {
        $this->_init('boleto_remittance_file', 'remittance_file_id');
    }
}
