<?php

namespace Gabrielqs\Boleto\Block\Adminhtml\Returns\View;

use \Magento\Backend\Block\Widget\Tabs;

class Tab extends Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('boleto_returns_file_info');
        $this->setDestElementId('main');
        $this->setTitle(__('Returns File Info'));
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
                'url' => $this->getUrl('boleto/returns/viewinfo', ['_current' => true]),
                'class' => 'ajax'
            ]
        );
        $this->addTab(
            'orders',
            [
                'label' => __('Orders'),
                'url' => $this->getUrl('boleto/returns/vieworders', ['_current' => true]),
                'class' => 'ajax'
            ]
        );
        $this->addTab(
            'events',
            [
                'label' => __('Events'),
                'url' => $this->getUrl('boleto/returns/viewevents', ['_current' => true]),
                'class' => 'ajax'
            ]
        );
        return parent::_prepareLayout();
    }
}