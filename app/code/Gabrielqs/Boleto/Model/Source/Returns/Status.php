<?php

namespace Gabrielqs\Boleto\Model\Source\Returns;

use \Magento\Framework\Data\OptionSourceInterface;
use \Gabrielqs\Boleto\Helper\Returns\Data as ReturnsHelper;

/**
 * Class Status
 */
class Status implements OptionSourceInterface
{
    /**
     * Returns Helper
     * @var ReturnsHelper
     */
    protected $_returnsHelper;

    /**
     * Constructor
     * @param ReturnsHelper $helper
     */
    public function __construct(ReturnsHelper $helper)
    {
        $this->_returnsHelper = $helper;
    }

    /**
     * Get options
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->_returnsHelper->getAllReturnsFileStatus();
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
