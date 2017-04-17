<?php

namespace Gabrielqs\Boleto\Block\Checkout\Success;

use \Magento\Framework\View\Element\Template;
use \Magento\Sales\Model\Order;
use \Magento\Framework\View\Element\Template\Context;
use \Magento\Checkout\Model\Session as CheckoutSession;
use \Gabrielqs\Boleto\Helper\Boleto\Data as BoletoHelper;

class PrintBoleto extends Template
{
    /**
     * Boleto Helper
     * @var BoletoHelper
     */
    protected $_boletoHelper;

    /**
     * Checkout Session
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * PrintBoleto constructor.
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param BoletoHelper $boletoHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        BoletoHelper $boletoHelper,
        array $data
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_boletoHelper = $boletoHelper;
        parent::__construct($context, $data);
    }

    /**
     * Retrieves last order from checkout session
     * @return Order
     */
    protected function _getOrder()
    {
        return $this->_checkoutSession->getLastRealOrder();
    }

    /**
     * Should we show the print boleto button on the success page?
     * @return bool
     */
    public function isShow()
    {
        $order = $this->_getOrder();
        return ($order->getPayment() && ($order->getPayment()->getMethod() == $this->_boletoHelper->getMethodCode()));
    }

    /**
     * Returns URL for boleto printing, using the last order placed
     * This function is to be used in the success page
     * @return string
     */
    public function getPrintUrl()
    {
        return $this->_boletoHelper->getPrintBoletoUrl($this->_getOrder());
    }
}