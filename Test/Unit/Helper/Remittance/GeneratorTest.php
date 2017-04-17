<?php

namespace Gabrielqs\Boleto\Test\Unit\Helper\Remittance;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Magento\Framework\Stdlib\DateTime\DateTime;
use \Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use \Magento\Sales\Model\Order as SalesOrder;
use \Magento\Sales\Model\Order\Address as SalesOrderAddress;
use \Magento\Sales\Model\ResourceModel\Order\Collection as SalesOrderCollection;
use \Cnab\Remessa\Cnab400\ArquivoFactory as RemittanceFileGeneratorFactory;
use \Cnab\Banco as RemittanceFileBank;
use \Gabrielqs\Boleto\Helper\Remittance\Generator as Subject;
use \Gabrielqs\Boleto\Helper\Boleto\Data as BoletoHelper;


/**
 * Unit Testcase
 */
class GeneratorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var String
     */
    protected $className = null;

    /**
     * @var ObjectManager
     */
    protected $objectManager = null;

    /**
     * @var Subject
     */
    protected $originalSubject = null;

    /**
     * @var Subject
     */
    protected $subject = null;

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

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->className = Subject::class;
        $arguments = $this->getConstructorArguments();

        $this->subject = $this
            ->getMockBuilder($this->className)
            ->setConstructorArgs($arguments)
            ->setMethods(null)
            ->getMock();

        $this->originalSubject = $this
            ->objectManager
            ->getObject($this->className, $arguments);
    }

    protected function getConstructorArguments()
    {
        $arguments = $this->objectManager->getConstructArguments($this->className);

        $this->boletoHelper = $this->getMockBuilder(BoletoHelper::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getRemittanceFileNameFormat',
                'getBankCode',
                'getBeneficiaryTradeName',
                'getBeneficiaryOfficialName',
                'getBeneficiaryTaxVat',
                'getBeneficiaryAddressStreet',
                'getBeneficiaryAddressNumber',
                'getBeneficiaryAddressNeighbourhood',
                'getBeneficiaryCity',
                'getBeneficiaryRegion',
                'getBeneficiaryPostcode',
                'getAgencyCode',
                'getAccountCode',
                'getAccountCodeDigit',
                'getContractCode',
                'getDescription',
                'getDaysToExpire',
                'convertOrderIncrementIdToBoletoId'
            ])
            ->getMock();
        $arguments['boletoHelper'] = $this->boletoHelper;

        $this->dateFactory = $this->getMockBuilder(DateTimeFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $arguments['dateFactory'] = $this->dateFactory;

        $this->remittanceFileGeneratorFactory = $this->getMockBuilder(RemittanceFileGeneratorFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $arguments['remittanceFileGeneratorFactory'] = $this->remittanceFileGeneratorFactory;

        return $arguments;
    }

    public function testGenerateFileNameCallsHelperAndUsesDateTimeModel()
    {
        $date = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->setMethods(['date'])
            ->getMock();
        $this
            ->dateFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($date);

        $this
            ->boletoHelper
            ->expects($this->once())
            ->method('getRemittanceFileNameFormat')
            ->willReturn('FooBarFormat.REM');

        $date
            ->expects($this->once())
            ->method('date')
            ->with('FooBarFormat.REM', null)
            ->willReturn('FooBarReturn');

        $this->assertEquals('FooBarReturn', $this->subject->generateFileName());
    }

    public function dataProviderTestGenerateFileContent()
    {
        return [
            ['bradesco', RemittanceFileBank::BRADESCO, $this->expectedBradescoRemittanceFile()],
            ['itau', RemittanceFileBank::ITAU, $this->expectedItauRemittanceFile()]
        ];
    }

    /**
     * @dataProvider dataProviderTestGenerateFileContent
     */
    public function testGenerateFileContent($bankCode, $bankDigitCode, $expectedResult)
    {
        $billingAddressA = $this->getMockBuilder(SalesOrderAddress::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getVatId',
                'getStreetLine',
                'getCity',
                'getRegionCode',
                'getPostcode'
            ])->getMock();
        $billingAddressA
            ->expects($this->once())
            ->method('getVatId')
            ->willReturn('039.392.383-23');
        $billingAddressA
            ->expects($this->exactly(3))
            ->method('getStreetLine')
            ->withConsecutive(
                [1],
                [2],
                [3]
            )
            ->willReturnOnConsecutiveCalls(
                'SQN 210 Bloco A Apto',
                '115',
                'Asa Norte'
            );
        $billingAddressA
            ->expects($this->once())
            ->method('getCity')
            ->willReturn('Brasília');
        $billingAddressA
            ->expects($this->once())
            ->method('getRegionCode')
            ->willReturn('DF');
        $billingAddressA
            ->expects($this->once())
            ->method('getPostcode')
            ->willReturn('70862-030');
        $orderA = $this->getMockBuilder(SalesOrder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIncrementId', 'getBillingAddress', 'getGrandTotal', 'getCustomerName'])
            ->getMock();
        $orderA
            ->expects($this->any())
            ->method('getCustomerName')
            ->willReturn('João Silva');
        $orderA
            ->expects($this->any())
            ->method('getIncrementId')
            ->willReturn('000002032');
        $orderA
            ->expects($this->once())
            ->method('getGrandTotal')
            ->willReturn(189.23);
        $orderA
            ->expects($this->once())
            ->method('getBillingAddress')
            ->willReturn($billingAddressA);

        $billingAddressB = $this->getMockBuilder(SalesOrderAddress::class)
                ->disableOriginalConstructor()
                ->setMethods([
                    'getVatId',
                    'getStreetLine',
                    'getCity',
                    'getRegionCode',
                    'getPostcode'
                ])->getMock();
        $billingAddressB
            ->expects($this->once())
            ->method('getVatId')
            ->willReturn('348.998.233-45');
        $billingAddressB
            ->expects($this->exactly(3))
            ->method('getStreetLine')
            ->withConsecutive(
                [1],
                [2],
                [3]
            )
            ->willReturnOnConsecutiveCalls(
                'Rua Augusta',
                '345',
                'Centro'
            );
        $billingAddressB
            ->expects($this->once())
            ->method('getCity')
            ->willReturn('São Paulo');
        $billingAddressB
            ->expects($this->once())
            ->method('getPostcode')
            ->willReturn('01265-020');
        $billingAddressB
            ->expects($this->once())
            ->method('getRegionCode')
            ->willReturn('SP');
        $orderB = $this->getMockBuilder(SalesOrder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIncrementId', 'getBillingAddress', 'getGrandTotal', 'getCustomerName'])
            ->getMock();
        $orderB
            ->expects($this->any())
            ->method('getIncrementId')
            ->willReturn('000002049');
        $orderB
            ->expects($this->any())
            ->method('getCustomerName')
            ->willReturn('Maria Lima');
        $orderB
            ->expects($this->once())
            ->method('getGrandTotal')
            ->willReturn(1092.39);
        $orderB
            ->expects($this->once())
            ->method('getBillingAddress')
            ->willReturn($billingAddressB);
        $salesOrderCollection = $this->getMockBuilder(SalesOrderCollection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getItems'])
            ->getMock();
        $salesOrderCollection
            ->expects($this->once())
            ->method('getItems')
            ->willReturn([$orderA, $orderB]);

        $this
            ->boletoHelper
            ->expects($this->exactly(2))
            ->method('getBankCode')
            ->willReturn($bankCode);

        $this
            ->remittanceFileGeneratorFactory
            ->expects($this->once())
            ->method('create')
            ->with([
                'codigo_banco' => $bankDigitCode
            ])
            ->willReturn(new \Cnab\Remessa\Cnab400\Arquivo($bankDigitCode));

        $dateTime = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->setMethods(['date'])
            ->getMock();
        $this
            ->dateFactory
            ->expects($this->any())
            ->method('create')
            ->willReturn($dateTime);

        $dateTime
            ->expects($this->exactly(6))
            ->method('date')
            ->withConsecutive(
                ['Y-m-d h:i:s', null],
                ['Y-m-d h:i:s', null],
                ['Y-m-d h:i:s', null],
                ['Y-m-d h:i:s', null],
                ['Y-m-d h:i:s', null],
                ['Y-m-d h:i:s', null]
            )->willReturnOnConsecutiveCalls(
                '2016-09-19 11:30:32',
                '2016-09-19 16:45:56',
                '2016-09-19 16:45:56',
                '2016-09-19 16:45:56',
                '2016-09-19 16:45:56',
                '2016-09-19 16:45:56'
            );

        $this
            ->boletoHelper
            ->expects($this->once())
            ->method('getBeneficiaryTradeName')
            ->willReturn('Gabrielqs eCommerce');
        $this
            ->boletoHelper
            ->expects($this->once())
            ->method('getBeneficiaryOfficialName')
            ->willReturn('Gabrielqs Tecnologia, Design e Comunicação LTDA');
        $this
            ->boletoHelper
            ->expects($this->once())
            ->method('getBeneficiaryTaxVat')
            ->willReturn('23.039.938/0001-79');
        $this
            ->boletoHelper
            ->expects($this->once())
            ->method('getBeneficiaryAddressStreet')
            ->willReturn('CLN 213 Bloco C Sala');
        $this
            ->boletoHelper
            ->expects($this->once())
            ->method('getBeneficiaryAddressNumber')
            ->willReturn('204');
        $this
            ->boletoHelper
            ->expects($this->once())
            ->method('getBeneficiaryAddressNeighbourhood')
            ->willReturn('Asa Norte');
        $this
            ->boletoHelper
            ->expects($this->once())
            ->method('getBeneficiaryCity')
            ->willReturn('Brasília');
        $this
            ->boletoHelper
            ->expects($this->once())
            ->method('getBeneficiaryRegion')
            ->willReturn('DF');
        $this
            ->boletoHelper
            ->expects($this->once())
            ->method('getBeneficiaryPostcode')
            ->willReturn('70864-030');
        $this
            ->boletoHelper
            ->expects($this->once())
            ->method('getAgencyCode')
            ->willReturn('351');
        $this
            ->boletoHelper
            ->expects($this->once())
            ->method('getAccountCode')
            ->willReturn('3892');
        $this
            ->boletoHelper
            ->expects($this->once())
            ->method('getAccountCodeDigit')
            ->willReturn('3');

        $this
            ->boletoHelper
            ->expects($this->exactly(2))
            ->method('convertOrderIncrementIdToBoletoId')
            ->withConsecutive(
                [$orderA],
                [$orderB]
            )->willReturnOnConsecutiveCalls(
                '00002032',
                '00002049'
            );
        $this
            ->boletoHelper
            ->expects($this->exactly(4))
            ->method('getDaysToExpire')
            ->willReturn(4);
        $this
            ->boletoHelper
            ->expects($this->exactly(2))
            ->method('getContractCode')
            ->willReturn('9');

        $return = $this->subject->generateFileContent($salesOrderCollection);

        $this->assertEquals($expectedResult, str_replace("\r", '',$return));
    }

    public function expectedBradescoRemittanceFile()
    {
        $date = date('dmy');
        $return = <<<BRADESCO
01REMESSA01COBRANCA       035100038923        Gabrielqs eCommerce                237BRADESCO       190916                                                                                                                                                                                                                                                                                                      000001
10223039938000179035100038923    000000002032                 000020320000000000000009                     I0100002032  23091600000000189232370000099N1909162 0 0000000000000{$date}0000000000000000000000000000000000000000100003939238323JOAO SILVA                              SQN 210 BLOCO A APTO 115                ASA NORTE   70862030BRASILIA       DFGABRIELQS ECOMMERCE                    00000400 000002
10223039938000179035100038923    000000002049                 000020490000000000000009                     I0100002049  23091600000001092392370000099N1909162 0 0000000000000{$date}0000000000000000000000000000000000000000100034899823345MARIA LIMA                              RUA AUGUSTA 345                         CENTRO      01265020SAO PAULO      SPGABRIELQS ECOMMERCE                    00000400 000003
9                                                                                                                                                                                                                                                                                                                                                                                                         000004

BRADESCO;
        return $return;

    }

    public function expectedItauRemittanceFile()
    {
        $date = date('dmy');
        $return = <<<ITAU
01REMESSA01COBRANCA       035100038923        Gabrielqs eCommerce                341BANCO ITAU SA  190916                                                                                                                                                                                                                                                                                                      000001
10223039938000179035100038923    000000002032                 000020320000000000000009                     I0100002032  23091600000000189233410000099N1909162 0 0000000000000{$date}0000000000000000000000000000000000000000100003939238323JOAO SILVA                              SQN 210 BLOCO A APTO 115                ASA NORTE   70862030BRASILIA       DFGABRIELQS ECOMMERCE                    00000400 000002
10223039938000179035100038923    000000002049                 000020490000000000000009                     I0100002049  23091600000001092393410000099N1909162 0 0000000000000{$date}0000000000000000000000000000000000000000100034899823345MARIA LIMA                              RUA AUGUSTA 345                         CENTRO      01265020SAO PAULO      SPGABRIELQS ECOMMERCE                    00000400 000003
9                                                                                                                                                                                                                                                                                                                                                                                                         000004

ITAU;
        return $return;

    }
}