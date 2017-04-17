<?php

namespace Gabrielqs\Boleto\Helper\Remittance;

use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\App\Helper\Context;
use \Magento\Framework\Stdlib\DateTime\DateTime;
use \Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use \Magento\Framework\Exception\LocalizedException;
use \Magento\Sales\Model\ResourceModel\Order\Collection as SalesOrderCollection;
use \Magento\Sales\Model\Order as SalesOrder;
use \Cnab\Banco as RemittanceFileBank;
use \Cnab\Remessa\Cnab400\ArquivoFactory as RemittanceFileGeneratorFactory;
use \Cnab\Especie as RemittanceFileOrderType;
use \Cnab\Remessa\Cnab400\Arquivo as RemittanceFileGenerator;
use \Gabrielqs\Boleto\Helper\Boleto\Data as BoletoHelper;
use \Gabrielqs\Boleto\Model\Source\Banks;

class Generator extends AbstractHelper
{
    /**
     * Constants used by CnabPHP lib when generating remittance files
     * @see https://github.com/andersondanilo/CnabPHP/wiki/Criando-um-arquivo-de-remessa
     */
    const INSTRUCTION_1 = 2;
    const INSTRUCTION_2 = 0;
    const ISSUING_CODE_TYPE = 1;
    const PAYER_TYPE_PERSON = 'cpf';
    const PAYER_TYPE_COMPANY = 'cnpj';
    const PERMANENCE_FARE = '0';

    /**
     * Boleto Helper
     * @var BoletoHelper
     */
    protected $boletoHelper = null;

    /**
     * Date Time Factory
     * @var DateTimeFactory $dateFactory
     */
    protected $dateFactory = null;

    /**
     * Remittance File Generator Factory
     * @var RemittanceFileGeneratorFactory $remittanceFileGeneratorFactory
     */
    protected $remittanceFileGeneratorFactory = null;

    /**
     * Generator constructor.
     * @param Context $context
     * @param BoletoHelper $boletoHelper
     * @param DateTimeFactory $dateFactory
     * @param RemittanceFileGeneratorFactory $remittanceFileGeneratorFactory
     */
    public function __construct(
        Context $context,
        BoletoHelper $boletoHelper,
        DateTimeFactory $dateFactory,
        RemittanceFileGeneratorFactory $remittanceFileGeneratorFactory
    ) {
        parent::__construct($context);
        $this->boletoHelper = $boletoHelper;
        $this->dateFactory = $dateFactory;
        $this->remittanceFileGeneratorFactory = $remittanceFileGeneratorFactory;
    }

    /**
     * Generates Remittance File Name
     * @return string
     */
    public function generateFileName()
    {
        /** @var DateTime $date */
        $date = $this->dateFactory->create();
        $format = $this->boletoHelper->getRemittanceFileNameFormat();
        return $date->date($format);
    }

    /**
     * Generates Remittance File Content
     * @param SalesOrderCollection $orders
     * @return string
     */
    public function generateFileContent(SalesOrderCollection $orders)
    {
        $remittanceFileGenerator = $this->_getRemittanceGeneratorObject();
        $date = $this->dateFactory->create();

        /** @var SalesOrder $order */
        foreach ($orders->getItems() as $order) {
            $billingAddress = $order->getBillingAddress();
            $boletoId = $this->boletoHelper->convertOrderIncrementIdToBoletoId($order);
            $remittanceFileGenerator->insertDetalhe([
                'codigo_ocorrencia' => self::ISSUING_CODE_TYPE,
                'nosso_numero'      => $boletoId,
                'numero_documento'  => $boletoId,
                'carteira'          => $this->boletoHelper->getContractCode(),
                'especie'           => $this->_getOrderType(),
                'valor'             => $order->getGrandTotal(),
                'instrucao1'        => self::INSTRUCTION_1,
                'instrucao2'        => self::INSTRUCTION_2,
                'sacado_nome'       => $order->getCustomerName(),
                'sacado_tipo'       => $this->_getPayerType($order),
                'sacado_cpf'        => $billingAddress->getVatId(),
                'sacado_logradouro' => $billingAddress->getStreetLine(1) . ' ' . $billingAddress->getStreetLine(2),
                'sacado_bairro'     => $billingAddress->getStreetLine(3),
                'sacado_cep'        => $this->_preparePostcode($billingAddress->getPostcode()),
                'sacado_cidade'     => $billingAddress->getCity(),
                'sacado_uf'         => $billingAddress->getRegionCode(),
                'data_vencimento'   => $this->_getBoletoExpiryDate(),
                'data_cadastro'     => $date->date('Y-m-d h:i:s'),
                'prazo'               => 0,
                'taxa_de_permanencia' => self::PERMANENCE_FARE,
                'mensagem'            => $this->boletoHelper->getDescription(),

                # The following fields are here because they are mandatory, but none of the info is actually used
                'data_multa'          => $this->boletoHelper->getDaysToExpire(),
                'valor_multa'         => 0,
                'juros_de_um_dia'     => 0,
                'data_desconto'       => $this->_getDateTimeInstance(),
                'valor_desconto'      => 0.0,
            ]);
        }

        return $remittanceFileGenerator->getText();
    }

    /**
     * Returns Boleto Expiry Date
     * @return string
     */
    protected function _getBoletoExpiryDate()
    {
        $date = $this->dateFactory->create();
        $expiryDateString = $date->date('Y-m-d h:i:s') . '+' . $this->boletoHelper->getDaysToExpire() . ' days';
        return $expiryDateString;
    }

    /**
     * Returns a date time object instance
     * @param null|string $format
     * @return \DateTime
     */
    protected function _getDateTimeInstance($format = null)
    {
        return new \DateTime($format);
    }

    /**
     * Returns the kind of the order being processed. Using a default info for all banks, will change
     * in the future if needed
     * @return string
     */
    protected function _getOrderType()
    {
        return RemittanceFileOrderType::CNAB240_OUTROS;
    }

    /**
     * Returns the bank code used by the remittance generator lib
     * @return int
     * @throws LocalizedException
     */
    protected function _getRemittanceGeneratorBankCode()
    {
        $bankCode = $this->boletoHelper->getBankCode();
        switch ($bankCode) {
            case Banks::BANK_CODE_BRADESCO:
                $remittanceGeneratorLibBankCode = RemittanceFileBank::BRADESCO;
                break;
            case Banks::BANK_CODE_ITAU:
                $remittanceGeneratorLibBankCode = RemittanceFileBank::ITAU;
                break;
            default:
                throw new LocalizedException(__('No bank was configured to be used on remittance file generation'));
                break;
        }
        return $remittanceGeneratorLibBankCode;
    }

    /**
     * Creates an instance of the remittance file generator model based on the configured bank to be used
     * @return RemittanceFileGenerator
     * @throws LocalizedException
     */
    protected function _getRemittanceGeneratorObject()
    {
        $remittanceGeneratorLibBankCode = $this->_getRemittanceGeneratorBankCode();
        /** @var RemittanceFileGenerator $remittanceFileGenerator */
        $remittanceFileGenerator = $this->remittanceFileGeneratorFactory->create([
            'codigo_banco' => $remittanceGeneratorLibBankCode
        ]);
        $this->_initRemittanceGeneratorObject($remittanceFileGenerator);
        return $remittanceFileGenerator;
    }

    /**
     * Initializes the
     * @param RemittanceFileGenerator $remittanceFileGenerator
     * @return RemittanceFileGenerator $remittanceFileGenerator
     */
    protected function _initRemittanceGeneratorObject(RemittanceFileGenerator $remittanceFileGenerator)
    {
        /** @var DateTime $date */
        $date = $this->dateFactory->create();
        $remittanceFileGenerator->configure([
            'data_geracao'  => $this->_getDateTimeInstance($date->date('Y-m-d h:i:s')),
            'data_gravacao' => $this->_getDateTimeInstance($date->date('Y-m-d h:i:s')),
            'nome_fantasia' => $this->boletoHelper->getBeneficiaryTradeName(),
            'razao_social'  => $this->boletoHelper->getBeneficiaryOfficialName(),
            'cnpj'          => $this->boletoHelper->getBeneficiaryTaxVat(),
            'banco'         => $this->_getRemittanceGeneratorBankCode(),
            'logradouro'    => $this->boletoHelper->getBeneficiaryAddressStreet(),
            'numero'        => $this->boletoHelper->getBeneficiaryAddressNumber(),
            'bairro'        => $this->boletoHelper->getBeneficiaryAddressNeighbourhood(),
            'cidade'        => $this->boletoHelper->getBeneficiaryCity(),
            'uf'            => $this->boletoHelper->getBeneficiaryRegion(),
            'cep'           => $this->boletoHelper->getBeneficiaryPostcode(),
            'agencia'       => $this->boletoHelper->getAgencyCode(),
            'conta'         => $this->boletoHelper->getAccountCode(),
            'conta_dac'     => $this->boletoHelper->getAccountCodeDigit(),
        ]);
        return $remittanceFileGenerator;
    }

    /**
     * Removes non digits from postcode (library requirement)
     * @param string $postcode
     * @return string
     */
    protected function _preparePostcode($postcode)
    {
        return (string) preg_replace('/\D/', '', $postcode);
    }

    /**
     * Type of the payer (person/company)
     * @param SalesOrder $order
     * @todo When the PJ module is done, we must change this method in order to accept b2b payments
     * @return string
     */
    protected function _getPayerType($order)
    {
        return self::PAYER_TYPE_PERSON;
    }
}