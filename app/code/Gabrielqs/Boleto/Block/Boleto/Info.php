<?php

namespace Gabrielqs\Boleto\Block\Boleto;

use \Magento\Payment\Block\Info as AbstractInfo;
use \Magento\Framework\View\Element\Template\Context;
use \Magento\Sales\Model\Order;
use \Gabrielqs\Boleto\Helper\Boleto\Data as BoletoHelper;

class Info extends AbstractInfo
{
    /**
     * Boleto helper
     * @var BoletoHelper
     */
    protected $_boletoHelper = null;

    /**
     * Template
     * @var string
     */
    protected $_template = 'Gabrielqs_Boleto::boleto/info.phtml';

    /**
     * Constructor
     *
     * @param Context $context
     * @param BoletoHelper $boletoHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        BoletoHelper $boletoHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_boletoHelper = $boletoHelper;
    }

    /**
     * Retrieves order from payment info
     * @return Order
     */
    protected function _getOrder()
    {
        return $this->getInfo()->getOrder();
    }

    /**
     * Returns print boleto URL
     * @return string
     */
    public function getPrintUrl()
    {
        $order = $this->_getOrder();
        $url = $this->_boletoHelper->getPrintBoletoUrl($order);
        return $url;
    }
}