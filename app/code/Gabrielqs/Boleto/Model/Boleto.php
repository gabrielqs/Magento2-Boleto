<?php

namespace Gabrielqs\Boleto\Model;

use \Magento\Framework\Exception\LocalizedException;
use \Magento\Payment\Model\InfoInterface;
use \Magento\Framework\Model\Context;
use \Magento\Framework\Registry;
use \Magento\Framework\Api\ExtensionAttributesFactory;
use \Magento\Framework\Api\AttributeValueFactory;
use \Magento\Payment\Helper\Data;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Payment\Model\Method\Logger;
use \Magento\Framework\Model\ResourceModel\AbstractResource;
use \Magento\Framework\Data\Collection\AbstractDb;
use \Magento\Directory\Model\CountryFactory;
use \Magento\Quote\Api\Data\CartInterface;
use \Magento\Payment\Model\Method\AbstractMethod as AbstractPaymentMethod;
use \Gabrielqs\Boleto\Helper\Boleto\Data as BoletoHelper;
use \Gabrielqs\Boleto\Helper\Boleto\Generator as BoletoGenerator;

/**
 * Boleto Payment Method
 */
class Boleto extends AbstractPaymentMethod
{

    const CODE = 'boleto_boleto';

    /**
     * Boleto Info Block
     * @var string
     */
    protected $_infoBlockType = 'Gabrielqs\Boleto\Block\Boleto\Info';

    /**
     * Can Authorize
     * @var bool
     */
    protected $_canAuthorize                = false;

    /**
     * Can Capture
     * @var bool
     */
    protected $_canCapture                  = true;

    /**
     * Can Capture Partial
     * @var bool
     */
    protected $_canCapturePartial           = false;

    /**
     * Can Order
     * @var bool
     */
    protected $_canOrder                    = true;

    /**
     * Can Refund
     * @var bool
     */
    protected $_canRefund                   = false;

    /**
     * Can Refund Partial
     * @var bool
     */
    protected $_canRefundInvoicePartial     = false;

    /**
     * Boleto Payment Method Code
     * @var string
     */
    protected $_code                        = self::CODE;

    /**
     * Is Payment Gateway?
     * @var bool
     */
    protected $_isGateway                   = true;

    /**
     * Is Offline Payment=
     * @var bool
     */
    protected $_isOffline                   = true;

    /**
     * Supported Currency Codes
     * @var string[]
     */
    protected $_supportedCurrencyCodes      = ['BRL'];

    /**
     * Country Factory
     * @var CountryFactory|null
     */
    protected $_countryFactory              = null;

    /**
     * Boleto Helper
     * @var BoletoHelper|null
     */
    protected $_boletoHelper            = null;

    /**
     * Boleto Generator Helper
     * @var BoletoGenerator|null
     */
    protected $_boletoGenerator            = null;

    /**
     * Boleto Helper
     * @var BoletoHelper|null
     */
    protected $_installmentsHelper          = null;

    /**
     * Boleto constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param Data $paymentData
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param BoletoHelper $boletoHelper
     * @param BoletoGenerator $boletoGenerator
     * @param CountryFactory $countryFactory
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        BoletoHelper $boletoHelper,
        BoletoGenerator $boletoGenerator,
        CountryFactory $countryFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_countryFactory = $countryFactory;
        $this->_boletoHelper = $boletoHelper;
        $this->_boletoGenerator = $boletoGenerator;
        return parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Availability for currency
     *
     * @param string $currencyCode
     * @return bool
     */
    public function canUseForCurrency($currencyCode)
    {
        if (!in_array($currencyCode, (array) $this->_supportedCurrencyCodes)) {
            return false;
        }
        return true;
    }

    /**
     * Determine method availability based on quote amount and config data
     *
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(CartInterface $quote = null)
    {
        if (!$this->getConfigData('active')) {
            return false;
        }

        if (($quote === null) || ($quote && ($quote->getBaseGrandTotal() <= $this->getConfigData('min_order_total')) ||
                (
                    $this->getConfigData('max_order_total') &&
                    $quote->getBaseGrandTotal() > $this->getConfigData('max_order_total')
                )
            )
        ) {
            return false;
        }

        return parent::isAvailable($quote);
    }

    /**
     * Payment Order
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws LocalizedException
     */
    public function order(InfoInterface $payment, $amount)
    {
        $order = $payment->getOrder();

        $boletoHtml = $this->_boletoGenerator->getBoletoHtml($order);

        $payment
            ->setAmount($amount)
            ->setStatus(self::STATUS_SUCCESS)
            ->setIsTransactionPending(false)
            ->setAdditionalInformation('boleto_html', $boletoHtml);

        return $this;
    }
}
