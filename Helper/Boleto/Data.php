<?php

namespace Gabrielqs\Boleto\Helper\Boleto;

use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Store\Model\ScopeInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Checkout\Model\Session as CheckoutSession;
use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\App\Helper\Context;
use \Magento\Sales\Model\Order;
use \Magento\Sales\Model\OrderFactory;
use \Magento\Directory\Model\RegionFactory;
use \Magento\Directory\Model\Region;
use \Magento\SalesSequence\Model\ResourceModel\Meta as SequenceMetadataMetadataResource;
use \Gabrielqs\Boleto\Model\Boleto;

class Data extends AbstractHelper
{
    /**
     * Maximum number of chars allowed in the boleto ID (nosso numero) field
     */
    const MAX_BOLETO_ID_CHARS = 8;

    /**
     * Maximum number of chars allowed in the boleto ID (nosso numero) field
     */
    const ORDER_INCREMENT_ID_CHARS = 9;

    /**
     * Store manager interface
     * @var StoreManagerInterface $_storeManager
     */
    protected $_storeManager = null;

    /**
     * Core store config
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig = null;

    /**
     * Order Factory
     * @var OrderFactory
     */
    protected $_orderFactory = null;

    /**
     * Region Factory
     * @var RegionFactory
     */
    protected $_regionFactory = null;

    /**
     * Sequence meta data resource
     * @var SequenceMetadataMetadataResource
     */
    protected $_sequenceMetadataResource;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param RegionFactory $regionFactory
     * @param OrderFactory $orderFactory
     * @param SequenceMetadataMetadataResource $sequenceMetadataResource
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        RegionFactory $regionFactory,
        OrderFactory $orderFactory,
        SequenceMetadataMetadataResource $sequenceMetadataResource
    ) {
        $this->_scopeConfig  = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_regionFactory = $regionFactory;
        $this->_orderFactory = $orderFactory;
        $this->_sequenceMetadataResource = $sequenceMetadataResource;
        parent::__construct($context);
    }

    /**
     * Converts the boleto id (nosso numero) to order increment id
     * @param string $boletoId
     * @return string
     */
    public function convertBoletoIdToOrderIncrementId($boletoId)
    {
        # Workaround (for a good reason)
        # The CnabPHP library returns boleto id (nosso numero) as an int
        # As in default config magento's orders are formatted such as 00000010, it would return something
        # we couldn't convert back to an order id (i.e int 10). The next if simply checks if that is the case, and
        # reformats the value back to magento's way
        if ($boletoId < 10000000) {
            $boletoId = str_pad($boletoId, self::ORDER_INCREMENT_ID_CHARS, '0', STR_PAD_LEFT);
        }

        if (preg_match('/^0(\d+)/i', $boletoId)) {
            # If the first digit is 0, there was no prefix configured when the boleto was issued
            $incrementIdPrefix = '';
        } else {
            # Otherwise, the prefix will be the first chars before we hit a zero (it seems the prefix
            # is actually alphanumeric...
            $matches = [];
            preg_match('/^([a-z1-9]+)0(\d+)/i', $boletoId, $matches);
            $incrementIdPrefix = $matches[1];
        }

        # Here we remove the prefix from the boleto id (we will add it back again later on)
        $incrementIdWithNoPrefix = preg_replace('/^' . $incrementIdPrefix . '/i', '', $boletoId);

        # We pad the order number with no prefix back to its original size and prepend the prefix back
        # to it's place
        $incrementId = $incrementIdPrefix .
            str_pad($incrementIdWithNoPrefix, self::ORDER_INCREMENT_ID_CHARS, '0', STR_PAD_LEFT);

        return $incrementId;
    }

    /**
     * Converts the order increment id to something we can use on the boleto
     * (Max 8 chars for some banks)
     * @param Order $order
     * @return string
     */
    public function convertOrderIncrementIdToBoletoId(Order $order)
    {
        # Getting the order id we will work with
        $incrementId = $order->getIncrementId();

        # Find the configured order prefix for the current store
        $prefix = $this->_getOrderSequencePrefix($order);
        $prefixLength = strlen($prefix);

        # According to the prefix length, less chars from the original order id will be allowed into the boleto
        # This will allow us later on to find out from which store view the order came from
        $resultLengthWithoutPrefix = self::MAX_BOLETO_ID_CHARS - $prefixLength;

        # Removing the prefix from the increment id (will be added later on)
        $return =  preg_replace('/' . $prefix . '/i', '', $incrementId);

        # Setting the return to a state where we can concat the prefix at the beginning and be in the
        # max allowed chars limit
        $return = substr($return, (-1 * $resultLengthWithoutPrefix));

        # Adding the prefix back to the id
        $return = $prefix . $return;

        return $return;
    }

    /**
     * Retrieves account code from configuration
     * @return string
     */
    public function getAccountCode()
    {
        return $this->getConfigData('account_code');
    }

    /**
     * Retrieves account code digit from configuration
     * @return string
     */
    public function getAccountCodeDigit()
    {
        return $this->getConfigData('account_code_digit');
    }

    /**
     * Retrieves agency code from configuration
     * @return string
     */
    public function getAgencyCode()
    {
        return $this->getConfigData('agency_code');
    }

    /**
     * Retrieves agency code from configuration
     * @return string
     */
    public function getBankCode()
    {
        return $this->getConfigData('bank');
    }

    /**
     * Retrieves beneficiary address from store configuration
     * @return string
     */
    public function getBeneficiaryAddress()
    {
        return $this->getBeneficiaryAddressStreet() . ' ' .
               $this->getBeneficiaryAddressNumber() . ' ' .
               $this->getBeneficiaryAddressNeighbourhood();
    }

    /**
     * Retrieves beneficiary street address from store configuration
     * @return string
     */
    public function getBeneficiaryAddressStreet()
    {
        return trim((string) $this->_scopeConfig->getValue('general/store_information/street_line1'));
    }

    /**
     * Retrieves beneficiary address neighbourhood from store configuration
     * @return string
     */
    public function getBeneficiaryAddressNeighbourhood()
    {
        return trim((string) $this->_scopeConfig->getValue('general/store_information/street_line3'));
    }

    /**
     * Retrieves beneficiary address number from store configuration
     * @return string
     */
    public function getBeneficiaryAddressNumber()
    {
        return trim((string) $this->_scopeConfig->getValue('general/store_information/street_line2'));
    }

    /**
     * Retrieves beneficiary city from store configuration
     * @return string
     */
    public function getBeneficiaryCity()
    {
        return (string) $this->_scopeConfig->getValue('general/store_information/city');
    }

    /**
     * Retrieves beneficiary name from store configuration
     * @return string
     */
    public function getBeneficiaryOfficialName()
    {
        return (string) $this->_scopeConfig->getValue('general/store_information/official_name');
    }

    /**
     * Retrieves beneficiary name from store configuration
     * @return string
     */
    public function getBeneficiaryTradeName()
    {
        return (string) $this->_scopeConfig->getValue('general/store_information/name');
    }

    /**
     * Retrieves beneficiary postcode from store configuration
     * @return string
     */
    public function getBeneficiaryPostcode()
    {
        return (string) $this->_scopeConfig->getValue('general/store_information/postcode');
    }

    /**
     * Retrieves beneficiary region from store configuration
     * @return string
     */
    public function getBeneficiaryRegion()
    {
        $regionId = (int) $this->_scopeConfig->getValue('general/store_information/region_id');
        /* @var Region $regionModel */
        $regionModel = $this->_regionFactory->create()->load($regionId);
        return (string) $regionModel->getCode();
    }

    /**
     * Retrieves beneficiary taxvat from store configuration
     * @return string
     */
    public function getBeneficiaryTaxVat()
    {
        return (string) $this->_scopeConfig->getValue('general/store_information/merchant_vat_number');
    }

    /**
     * Returns Boleto Payment Method System Config
     * @param string $field
     * @param null $storeId
     * @return array|string
     */
    public function getConfigData($field, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->_storeManager->getStore(null);
        }
        $path = 'payment/' . $this->getMethodCode() . '/' . $field;
        return $this->_scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Retrieves contract (carteira) code from configuration
     * @return string
     */
    public function getContractCode()
    {
        return $this->getConfigData('contract_code');
    }

    /**
     * Retrieves days to expire from configuration
     * @return string
     */
    public function getDaysToExpire()
    {
        return (int) $this->getConfigData('days_to_expire');
    }

    /**
     * Retrieves description from configuration
     * @return string
     */
    public function getDescription()
    {
        return $this->getConfigData('description');
    }

    /**
     * Retrieves instructions from configuration
     * @return string
     */
    public function getInstructions()
    {
        return $this->getConfigData('instructions');
    }

    /**
     * Returns Boleto Boleto Method Code
     * @return string
     */
    public function getMethodCode()
    {
        return Boleto::CODE;
    }

    /**
     * Retrieves an order id from a given opKey
     * Checks whether the info is valid, i.e. order id and customer id match
     * @param string $opKey
     * @return Order|null
     */
    public function getOrderFromOpKey($opKey)
    {
        $return = null;

        $opkeyData = @unserialize(base64_decode($opKey));
        if (
            is_array($opkeyData) &&
            array_key_exists('o', $opkeyData) && (((int) $opkeyData['o']) > 0) &&
            array_key_exists('p', $opkeyData) && (((int) $opkeyData['p']) > 0) &&
            array_key_exists('c', $opkeyData) && (($opkeyData['c']) != '')
        ) {
            $orderId = (int) $opkeyData['o'] ;
            $customerId = (int) $opkeyData['p'];
            $createdAt = $opkeyData['c'];

            /** @var Order $order */
            $order = $this->_orderFactory->create();
            $order->load($orderId);

            if (
               ($order->getId() == $orderId) &&
               ($order->getCustomerId() == $customerId) &&
               ($order->getCreatedAt() == $createdAt)
            ) {
                $return = $order;
            }
        }

        return $return;
    }

    /**
     * Gets the prefix for the order sequence
     * @param Order $order
     * @return string
     */
    protected function _getOrderSequencePrefix(Order $order)
    {
        $metadata = $this->_sequenceMetadataResource->loadByEntityTypeAndStore(
            Order::ENTITY,
            $order->getStoreId()
        );
        return $metadata->getPrefix();
    }

    /**
     * Returns Boleto Boleto Print URL
     * @param Order $order
     * @return string
     */
    public function getPrintBoletoUrl(Order $order)
    {
        $key = base64_encode(serialize([
            'o' => $order->getId(),
            'p' =>  $order->getCustomerId(),
            'c' => $order->getCreatedAt()
        ]));
        $params = [
            '_secure' => true,
            '_area' => 'frontend',
            'opkey' => $key
        ];
        return $this->_getUrl('boleto/boletoprint', $params);
    }

    /**
     * Retrieves Remittance File Name
     * @return string
     */
    public function getRemittanceFileNameFormat()
    {
        return $this->getConfigData('remittance_file_name_format');
    }

    /**
     * Should send e-mail notifying invoice creation?
     * @return bool
     */
    public function isSendInvoiceEmail()
    {
        return (bool) $this->getConfigData('send_invoice_email');
    }

    /**
     * Are we in test mode?
     * @return bool
     */
    public function isTest()
    {
        return (bool) $this->getConfigData('test_mode_enabled');
    }
}