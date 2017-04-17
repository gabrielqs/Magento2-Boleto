<?php
namespace Gabrielqs\Boleto\Model\Source;

use \Magento\Framework\Option\ArrayInterface;

/**
 * Class Boleto Banks - Returns all available banks for the Boleto Payment method
 * @package Gabrielqs\Boleto\Model\Source
 */
class Banks implements ArrayInterface
{
    /**
     * Itaú Bank Code
     */
    const BANK_CODE_ITAU     = 'itau';

    /**
     * Bradesco Bank Code
     */
    const BANK_CODE_BRADESCO = 'bradesco';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $options = [
            ['value' => self::BANK_CODE_ITAU, 'label' => 'Itaú'],
            ['value' => self::BANK_CODE_BRADESCO, 'label' => 'Bradesco']
        ];
        return $options;
    }
}