<?php

namespace Gabrielqs\Boleto\Model\ResourceModel\Returns\File;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Order extends AbstractDb
{
    /**
     * Returns File Order Resource Constructor
     * @return void
     */
    protected function _construct()
    {
        $this->_init('boleto_returns_file_order', 'returns_file_order_id');
    }
}
