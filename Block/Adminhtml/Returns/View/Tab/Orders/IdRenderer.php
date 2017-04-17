<?php

namespace Gabrielqs\Boleto\Block\Adminhtml\Returns\View\Tab\Orders;

use \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text as TextRenderer;
use \Magento\Sales\Model\OrderRepository;
use \Magento\Backend\Block\Context;
use \Magento\Framework\DataObject;

class IdRenderer extends TextRenderer
{
    /**
     * Order Repository
     * @var OrderRepository
     */
    protected $orderRepository = null;

    /**
     * IdRenderer constructor.
     * @param Context $context
     * @param OrderRepository $orderRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        OrderRepository $orderRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderRepository = $orderRepository;
    }

    /**
     * Gets an Increment id from an order entity_id
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $value = (int) $row->getData($this->getColumn()->getIndex());
        $order = $this->orderRepository->get($value);
        return $order->getIncrementId();
    }
}
