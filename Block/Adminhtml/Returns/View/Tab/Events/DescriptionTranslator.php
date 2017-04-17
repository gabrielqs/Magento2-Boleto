<?php

namespace Gabrielqs\Boleto\Block\Adminhtml\Returns\View\Tab\Events;

use \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text as TextRenderer;
use \Magento\Framework\DataObject;

class DescriptionTranslator extends TextRenderer
{

    /**
     * Translates DescriptionText
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());
        $translatedText = (string) __($value);
        return $translatedText;
    }
}
