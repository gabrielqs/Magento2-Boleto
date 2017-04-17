<?php

namespace Gabrielqs\Boleto\Controller\Boletoprint;

use \Magento\Framework\App\Action\Action;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\Controller\ResultFactory;
use \Magento\Framework\Controller\Result\Raw;
use \Magento\Framework\Exception\LocalizedException;
use \Magento\Checkout\Model\Session as CheckoutSession;
use \Magento\Sales\Model\Order;
use \Magento\Sales\Model\Order\Payment;
use \Gabrielqs\Boleto\Helper\Boleto\Data as BoletoHelper;

class Index extends Action
{
    /**
     * Boleto Helper
     * @var BoletoHelper
     */
    protected $_boletoHelper;

    /**
     * Result Factory
     * @var ResultFactory
     */
    protected $_resultFactory;

    /**
     * Index constructor.
     * @param Context $context
     * @param ResultFactory $rawResultFactory
     * @param BoletoHelper $boletoHelper
     */
    public function __construct(
        Context $context,
        ResultFactory $rawResultFactory,
        BoletoHelper $boletoHelper
    ) {
        $this->_resultFactory = $rawResultFactory;
        $this->_boletoHelper = $boletoHelper;
        return parent::__construct($context);
    }

    /**
     * Prints the boleto from informed order id
     * @return Raw
     * @throws LocalizedException
     */
    public function execute()
    {
        /* @var Order $order */
        $order = $this->_extractOrderFromRequest();
        /* @var Payment $order */
        $payment = $order->getPayment();
        $boletoHtml = $payment->getAdditionalInformation('boleto_html');

        if (!$boletoHtml) {
            throw new LocalizedException(__('There was no boleto info present in order.'));
        }

        return $this->_getRawResult($boletoHtml);
    }

    /**
     * Retrieves order from request
     * @throws LocalizedException
     * @return Order
     */
    protected function _extractOrderFromRequest()
    {
        $opKey = $this->_getOpkeyFromRequest();
        $order = $this->_boletoHelper->getOrderFromOpKey($opKey);
        if (!$order) {
            throw new LocalizedException(__('Order ID not informed, we can\'t print the Boleto.'));
        }
        return $order;
    }

    /**
     * Extracts OpKey From request
     * @return string|null
     */
    protected function _getOpkeyFromRequest()
    {
        return $this->_request->getParam('opkey');
    }

    /**
     * Creates a raw result with the generated Boleto HTML
     * @param string $html
     * @return Raw
     */
    protected function _getRawResult($html)
    {
        /* @var Raw $result */
        $result = $this->_resultFactory->create(ResultFactory::TYPE_RAW);
        $result->setHeader('Content-Type', 'text/html');
        $result->setContents($html);
        return $result;
    }
}
