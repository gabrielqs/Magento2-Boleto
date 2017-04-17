<?php

namespace Gabrielqs\Boleto\Helper\Returns;

use \Magento\Framework\App\Helper\AbstractHelper;
use \Gabrielqs\Boleto\Model\Returns\File as ReturnsFile;

class Data extends AbstractHelper
{
    /**
     * Returns all return file possible status
     * @return string[]
     */
    public function getAllReturnsFileStatus()
    {
        return [
            ReturnsFile::STATUS_NEW => __('New'),
            ReturnsFile::STATUS_ERROR => __('Error'),
            ReturnsFile::STATUS_SUCCESS => __('Success')
        ];
    }

    /**
     * Converts a status ID to its corresponding label
     * @param int $status
     * @return string
     */
    public function getStatusLabel($status)
    {
        $return = '-';
        foreach ($this->getAllReturnsFileStatus() as $statusId => $label) {
            if ($statusId == $status) {
                $return = $label;
                break;
            }
        }
        return $return;
    }
}