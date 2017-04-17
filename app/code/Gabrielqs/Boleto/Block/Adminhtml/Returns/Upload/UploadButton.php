<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Gabrielqs\Boleto\Block\Adminhtml\Returns\Upload;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class UploadButton
 */
class UploadButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Get Button Data
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Upload Return File'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ],
            'sort_order' => 90,
        ];
    }
}
