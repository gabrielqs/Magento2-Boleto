<?php

namespace Gabrielqs\Boleto\Model\ResourceModel\Remittance\File;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Order extends AbstractDb
{
    /**
     * Remittance File Order Resource Constructor
     * @return void
     */
    protected function _construct()
    {
        $this->_init('boleto_remittance_file_order', 'remittance_file_order_id');
    }
}
