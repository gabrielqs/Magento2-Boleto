<?php

namespace Gabrielqs\Boleto\Model\ResourceModel\Returns;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class File extends AbstractDb
{
    /**
     * Returns File Abstract Resource Constructor
     * @return void
     */
    protected function _construct()
    {
        $this->_init('boleto_returns_file', 'returns_file_id');
    }
}
