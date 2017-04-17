<?php

namespace Gabrielqs\Boleto\Helper\Boleto;

use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\Exception\LocalizedException;
use \Magento\Sales\Model\Order;
use \Magento\Framework\App\Helper\Context;
use \Magento\Framework\ObjectManagerInterface;
use \OpenBoleto\BoletoAbstract;
use \OpenBoleto\AgenteFactory as BoletoAgentFactory;
use \OpenBoleto\Agente as BoletoAgent;
use \Gabrielqs\Boleto\Helper\Boleto\Data as BoletoHelper;
use \Gabrielqs\Boleto\Model\Source\Banks;

class Generator extends AbstractHelper
{
    /**
     * Boleto Agent Factory
     * @var BoletoAgentFactory
     */
    protected $_boletoAgentFactory = null;

    /**
     * Boleto Helper
     * @var BoletoHelper
     */
    protected $_boletoHelper = null;

    /**
     * Boleto Model Factory
     * @var ObjectManagerInterface
     * @todo Try not to use object manager here (kind of impossible)
     */
    protected $_boletoModelFactory = null;

    /**
     * Generator constructor.
     * @param Context $context
     * @param BoletoAgentFactory $boletoAgentFactory
     * @param BoletoHelper $boletoHelper
     * @param ObjectManagerInterface $boletoModelFactory
     */
    public function __construct(
        Context $context,
        BoletoAgentFactory $boletoAgentFactory,
        BoletoHelper $boletoHelper,
        ObjectManagerInterface $boletoModelFactory
    ) {
        $this->_boletoAgentFactory = $boletoAgentFactory;
        $this->_boletoHelper = $boletoHelper;
        $this->_boletoModelFactory = $boletoModelFactory;
        parent::__construct($context);
    }

    /**
     * Returns a boleto agent from informed input
     * @param \stdClass $agentInfo
     * @return BoletoAgent
     * @throws LocalizedException
     */
    protected function _createBoletoAgent(\stdClass $agentInfo)
    {
        if (
            !isset($agentInfo->name) || empty($agentInfo->name) ||
            !isset($agentInfo->taxVat) || empty($agentInfo->taxVat) ||
            !isset($agentInfo->address) || empty($agentInfo->address) ||
            !isset($agentInfo->postcode) || empty($agentInfo->postcode) ||
            !isset($agentInfo->city) || empty($agentInfo->city) ||
            !isset($agentInfo->region) || empty($agentInfo->region)
        ) {
            throw new LocalizedException(__('We don\'t have the necessary information to create a boleto agent.'));
        }

        return $this->_boletoAgentFactory->create([
            'nome' => $agentInfo->name,
            'documento' => $agentInfo->taxVat,
            'endereco' => $agentInfo->address,
            'cep' => $agentInfo->postcode,
            'cidade' => $agentInfo->city,
            'uf' => $agentInfo->region
        ]);
    }

    /**
     * Creates a Boleto object with the given information
     * @param Order $order
     * @param BoletoAgent $beneficiary
     * @param BoletoAgent $payer
     * @return BoletoAbstract
     * @throws LocalizedException
     */
    protected function _createBoletoObject(Order $order, BoletoAgent $beneficiary, BoletoAgent $payer)
    {
        $bankCode = $this->_boletoHelper->getBankCode();
        switch ($bankCode) {
            case Banks::BANK_CODE_BRADESCO:
                $className = '\OpenBoleto\Banco\Bradesco';
                break;
            case Banks::BANK_CODE_ITAU:
                $className = '\OpenBoleto\Banco\Itau';
                break;
            default:
                throw new LocalizedException(__('No bank was configured to be used on boleto generation'));
                break;
        }
        $boletoModel = new $className($this->_getBoletoObjectParams($order, $beneficiary, $payer));
        return $boletoModel;
    }

    /**
     * Generates Beneficiary info, using data from Data Helper
     * @return \stdClass
     */
    protected function _generateBeneficiaryInfo()
    {
        $return = new \stdClass();

        $return->name = $this->_boletoHelper->getBeneficiaryOfficialName();
        $return->taxVat = $this->_boletoHelper->getBeneficiaryTaxVat();
        $return->address = $this->_boletoHelper->getBeneficiaryAddress();
        $return->postcode = $this->_boletoHelper->getBeneficiaryPostcode();
        $return->city = $this->_boletoHelper->getBeneficiaryCity();
        $return->region = $this->_boletoHelper->getBeneficiaryRegion();

        return $return;
    }

    /**
     * Generates Payer info, using data from the given order
     * @param Order $order
     * @return \stdClass
     */
    protected function _generatePayerInfo(Order $order)
    {
        $return = new \stdClass();

        $billingAddress = $order->getBillingAddress();

        $return->name = $order->getCustomerName();
        $return->taxVat = $billingAddress->getVatId();
        $return->address = implode($billingAddress->getStreet(), ', ');
        $return->postcode = $billingAddress->getPostcode();
        $return->city = $billingAddress->getCity();
        $return->region = $billingAddress->getRegionCode();

        return $return;
    }

    /**
     * Creates DateTime for boleto expiry date
     * @return \DateTime
     */
    protected function _getBoletoExpiryDate()
    {
        $daysToExpire = ((int) $this->_boletoHelper->getDaysToExpire());
        $daysToExpire = $daysToExpire ? $daysToExpire : 1;
        $expiryDate = new \DateTime(date('Y-m-d', strtotime('+' . $daysToExpire . ' days')));
        return $expiryDate;
    }

    /**
     * Returns HTML for boleto printing, converting from an order
     * @param Order $order
     * @return string
     */
    public function getBoletoHtml(Order $order)
    {
        $beneficiaryInfo = $this->_generateBeneficiaryInfo();
        $beneficiary = $this->_createBoletoAgent($beneficiaryInfo);

        $payerInfo = $this->_generatePayerInfo($order);
        $payer = $this->_createBoletoAgent($payerInfo);

        $boleto = $this->_createBoletoObject($order, $beneficiary, $payer);

        return $boleto->getOutput();
    }

    /**
     * Returns parameters for boleto object creation
     * @param Order $order
     * @param BoletoAgent $beneficiary
     * @param BoletoAgent $payer
     * @return array
     */
    protected function _getBoletoObjectParams(Order $order, BoletoAgent $beneficiary, BoletoAgent $payer)
    {
        return [
            'dataVencimento' => $this->_getBoletoExpiryDate(),
            'valor' => $order->getGrandTotal(),
            'sequencial' => $this->_prepareOrderId($order),
            'sacado' => $payer,
            'cedente' => $beneficiary,
            'agencia' => $this->_boletoHelper->getAgencyCode(),
            'carteira' => $this->_boletoHelper->getContractCode(),
            'conta' => $this->_boletoHelper->getAccountCode(),
            'descricaoDemonstrativo' => $this->_boletoHelper->getDescription(),
            'instrucoes' => $this->_boletoHelper->getInstructions(),
        ];
    }

    /**
     * Returns the last 8 chars from the order increment id, to be used when creating boletos
     * @param Order $order
     * @return string
     */
    protected function _prepareOrderId($order)
    {
        return $this->_boletoHelper->convertOrderIncrementIdToBoletoId($order);
    }
}