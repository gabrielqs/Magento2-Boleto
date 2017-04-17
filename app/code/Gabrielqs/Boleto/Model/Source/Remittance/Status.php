<?php

namespace Gabrielqs\Boleto\Model\Source\Remittance;

use \Magento\Framework\Data\OptionSourceInterface;
use \Gabrielqs\Boleto\Helper\Remittance\Data as RemittanceHelper;

/**
 * Class Status
 */
class Status implements OptionSourceInterface
{
    /**
     * Remittance Helper
     * @var RemittanceHelper
     */
    protected $_remittanceHelper;

    /**
     * Constructor
     * @param RemittanceHelper $helper
     */
    public function __construct(RemittanceHelper $helper)
    {
        $this->_remittanceHelper = $helper;
    }

    /**
     * Get options
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->_remittanceHelper->getAllRemittanceFileStatus();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
