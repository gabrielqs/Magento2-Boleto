<?php

namespace Gabrielqs\Boleto\Model\ResourceModel\Returns\File;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Event extends AbstractDb
{
    /**
     * Returns File Event Resource Constructor
     * @return void
     */
    protected function _construct()
    {
        $this->_init('boleto_returns_file_event', 'returns_file_event_id');
    }
}
