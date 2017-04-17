<?php

namespace Gabrielqs\Boleto\Block\Adminhtml\Remittance\View;

use \Magento\Backend\Block\Widget\Tabs;

class Tab extends Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('boleto_remittance_file_info');
        $this->setDestElementId('main');
        $this->setTitle(__('Remittance File Info'));
    }

    /**
     * Prepare Layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->addTab(
            'info',
            [
                'label' => __('Info'),
                'url' => $this->getUrl('boleto/remittance/viewinfo', ['_current' => true]),
                'class' => 'ajax'
            ]
        );
        $this->addTab(
            'orders',
            [
                'label' => __('Orders'),
                'url' => $this->getUrl('boleto/remittance/vieworders', ['_current' => true]),
                'class' => 'ajax'
            ]
        );
        $this->addTab(
            'events',
            [
                'label' => __('Events'),
                'url' => $this->getUrl('boleto/remittance/viewevents', ['_current' => true]),
                'class' => 'ajax'
            ]
        );
        return parent::_prepareLayout();
    }
}