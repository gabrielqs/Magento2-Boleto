<?php

namespace Gabrielqs\Boleto\Helper\Remittance;

use \Magento\Framework\App\Helper\AbstractHelper;
use \Gabrielqs\Boleto\Model\Remittance\File as RemittanceFile;

class Data extends AbstractHelper
{
    /**
     * Remittance all return file possible status
     * @return string[]
     */
    public function getAllRemittanceFileStatus()
    {
        return [
            RemittanceFile::STATUS_NEW => __('New'),
            RemittanceFile::STATUS_ERROR => __('Error'),
            RemittanceFile::STATUS_SUCCESS => __('Success')
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
        foreach ($this->getAllRemittanceFileStatus() as $statusId => $label) {
            if ($statusId == $status) {
                $return = $label;
                break;
            }
        }
        return $return;
    }
}