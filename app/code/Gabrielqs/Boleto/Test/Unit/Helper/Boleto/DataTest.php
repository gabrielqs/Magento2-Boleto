<?php

namespace Gabrielqs\Boleto\Test\Unit\Helper\Boleto;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Magento\Framework\App\Config as ScopeConfig;
use \Magento\Directory\Model\RegionFactory;
use \Magento\Directory\Model\Region;
use \Magento\Sales\Model\Order;
use \Magento\Sales\Model\OrderFactory;
use \Gabrielqs\Boleto\Helper\Boleto\Data as Subject;

/**
 * DataTest, Boleto Helper Testcase
 */
class DataTest extends \PHPUnit_Framework_TestCase
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
     * @var OrderFactory
     */
    protected $orderFactory = null;

    /**
     * @var Subject
     */
    protected $originalSubject = null;

    /**
     * @var RegionFactory
     */
    protected $regionFactory = null;

    /**
     * @var ScopeConfig
     */
    protected $scopeConfig = null;

    /**
     * @var Subject
     */
    protected $subject = null;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->className = Subject::class;
        $arguments = $this->getConstructorArguments();

        $this->subject = $this
            ->getMockBuilder($this->className)
            ->setConstructorArgs($arguments)
            ->setMethods(['getConfigData', '_getUrl', '_getOrderSequencePrefix'])
            ->getMock();

        $this->originalSubject = $this
            ->objectManager
            ->getObject($this->className, $arguments);
    }

    protected function getConstructorArguments()
    {
        $arguments = $this->objectManager->getConstructArguments($this->className);

        $this->scopeConfig = $this->getMockBuilder(ScopeConfig::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMock();
        $arguments['scopeConfig'] = $this->scopeConfig;

        $this->regionFactory = $this->getMockBuilder(RegionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $arguments['regionFactory'] = $this->regionFactory;

        $this->orderFactory = $this->getMockBuilder(OrderFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $arguments['orderFactory'] = $this->orderFactory;

        return $arguments;
    }

    public function testIsTestRetrievesCorrectKey()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('getConfigData')
            ->with('test_mode_enabled')
            ->willReturn($this->returnValue(true));

        $this->assertEquals(true, $this
            ->subject
            ->isTest()
        );
    }

    public function testReturnsCorrectMethodCode()
    {
        $this->assertEquals('boleto_boleto', $this->subject->getMethodCode());
    }

    public function testGetAccountCodeRetrievesCorrectKey()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('getConfigData')
            ->with('account_code')
            ->willReturn('0982');

        $this->assertEquals('0982', $this
            ->subject
            ->getAccountCode()
        );
    }

    public function testGetAccountCodeDigitRetrievesCorrectKey()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('getConfigData')
            ->with('account_code_digit')
            ->willReturn('2');

        $this->assertEquals('2', $this
            ->subject
            ->getAccountCodeDigit()
        );
    }

    public function testGetAgencyCodeRetrievesCorrectKey()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('getConfigData')
            ->with('agency_code')
            ->willReturn('09823');

        $this->assertEquals('09823', $this
            ->subject
            ->getAgencyCode()
        );
    }

    public function testGetBankCodeRetrievesCorrectKey()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('getConfigData')
            ->with('bank')
            ->willReturn('itau');

        $this->assertEquals('itau', $this
            ->subject
            ->getBankCode()
        );
    }

    public function testGetBeneficiaryAddressRetrievesCorrectKey()
    {
        $this
            ->scopeConfig
            ->expects($this->exactly(3))
            ->method('getValue')
            ->withConsecutive(
                ['general/store_information/street_line1', 'default', null],
                ['general/store_information/street_line2', 'default', null],
                ['general/store_information/street_line3', 'default', null]
            )
            ->willReturnOnConsecutiveCalls(
                $this->returnValue(' SQN 213 Bl. C Sala'),
                $this->returnValue('204 '),
                $this->returnValue('Asa Norte')
            );

        $this->assertEquals('SQN 213 Bl. C Sala 204 Asa Norte',
            $this
            ->subject
            ->getBeneficiaryAddress()
        );
    }

    public function testGetBeneficiaryCityRetrievesCorrectKey()
    {
        $this
            ->scopeConfig
            ->expects($this->once())
            ->method('getValue')
            ->with(
                'general/store_information/city', 'default', null
            )
            ->will($this->returnValue('Brasília'));

        $this->assertEquals('Brasília',
            $this
            ->subject
            ->getBeneficiaryCity()
        );
    }

    public function testGetBeneficiaryOfficialNameRetrievesCorrectKey()
    {
        $this
            ->scopeConfig
            ->expects($this->once())
            ->method('getValue')
            ->with(
                'general/store_information/official_name', 'default', null
            )
            ->will($this->returnValue('Gabrielqs Tecnologia Design LTDA'));

        $this->assertEquals('Gabrielqs Tecnologia Design LTDA',
            $this
            ->subject
            ->getBeneficiaryOfficialName()
        );
    }

    public function testGetBeneficiaryTradeNameRetrievesCorrectKey()
    {
        $this
            ->scopeConfig
            ->expects($this->once())
            ->method('getValue')
            ->with(
                'general/store_information/name', 'default', null
            )
            ->will($this->returnValue('Gabrielqs eCommerce'));

        $this->assertEquals('Gabrielqs eCommerce',
            $this
            ->subject
            ->getBeneficiaryTradeName()
        );
    }

    public function testGetBeneficiaryPostcodeRetrievesCorrectKey()
    {
        $this
            ->scopeConfig
            ->expects($this->once())
            ->method('getValue')
            ->with(
                'general/store_information/postcode', 'default', null
            )
            ->will($this->returnValue('70862-030'));

        $this->assertEquals('70862-030',
            $this
            ->subject
            ->getBeneficiaryPostcode()
        );
    }

    public function testGetBeneficiaryRegionRetrievesCorrectKey()
    {
        $this
            ->scopeConfig
            ->expects($this->once())
            ->method('getValue')
            ->with(
                'general/store_information/region_id', 'default', null
            )
            ->will($this->returnValue('10'));

        $region = $this->getMockBuilder(Region::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getCode'])
            ->getMock();

        $region
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($region));

        $region
            ->expects($this->once())
            ->method('getCode')
            ->will($this->returnValue('DF'));

        $this
            ->regionFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($region));

        $this->assertEquals('DF',
            $this
            ->subject
            ->getBeneficiaryRegion()
        );
    }

    public function testGetBeneficiaryTaxVatRetrievesCorrectKey()
    {
        $this
            ->scopeConfig
            ->expects($this->once())
            ->method('getValue')
            ->with(
                'general/store_information/merchant_vat_number', 'default', null
            )
            ->will($this->returnValue('10.202.193/0001-28'));

        $this->assertEquals('10.202.193/0001-28',
            $this
                ->subject
                ->getBeneficiaryTaxVat()
        );
    }

    public function testGetContractCodeRetrievesCorrectKey()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('getConfigData')
            ->with('contract_code')
            ->willReturn('03223');

        $this->assertEquals('03223', $this
            ->subject
            ->getContractCode()
        );
    }

    public function testGetDaysToExpireRetrievesCorrectKey()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('getConfigData')
            ->with('days_to_expire')
            ->willReturn('3');

        $this->assertEquals(3, $this
            ->subject
            ->getDaysToExpire()
        );
    }

    public function testGetDescriptionRetrievesCorrectKey()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('getConfigData')
            ->with('description')
            ->willReturn('Pedido efetuado na loja virtual X.');

        $this->assertEquals('Pedido efetuado na loja virtual X.', $this
            ->subject
            ->getDescription()
        );
    }

    public function testGetInstructionsRetrievesCorrectKey()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('getConfigData')
            ->with('instructions')
            ->willReturn('Não receber após vencimento.');

        $this->assertEquals('Não receber após vencimento.', $this
            ->subject
            ->getInstructions()
        );
    }

    public function testGetRemittanceFileFormatRetrievesCorrectKey()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('getConfigData')
            ->with('remittance_file_name_format')
            ->willReturn('\R\MY-m-d\.REM');

        $this->assertEquals('\R\MY-m-d\.REM', $this
            ->subject
            ->getRemittanceFileNameFormat()
        );
    }

    public function testIsSendInvoiceEmailRetrievesCorrectKey()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('getConfigData')
            ->with('send_invoice_email')
            ->willReturn('1');

        $this->assertEquals(true, $this
            ->subject
            ->isSendInvoiceEmail()
        );
    }

    public function testGetPrintBoletoUrlReturnsExpectedValue()
    {
        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getCustomerId', 'getCreatedAt'])
            ->getMock();

        $order
            ->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(1095));
        $order
            ->expects($this->once())
            ->method('getCustomerId')
            ->will($this->returnValue(560));
        $order
            ->expects($this->once())
            ->method('getCreatedAt')
            ->will($this->returnValue('2016-09-05 17:12:30'));

        $mockKey = base64_encode(serialize([
            'o' => 1095,
            'p' =>  560,
            'c' => '2016-09-05 17:12:30'
        ]));

        $params = [
            '_secure' => true,
            '_area' => 'frontend',
            'opkey' => $mockKey
        ];

        $this
            ->subject
            ->expects($this->once())
            ->method('_getUrl')
            ->with('boleto/boletoprint', $params);

        $this->subject->getPrintBoletoUrl($order);
    }

    public function testGetOrderFromOpkeyReturnsFalseWhenOpKeyIsNotAnArray()
    {
        $mockKey = base64_encode(serialize('test'));
        $return = $this->subject->getOrderFromOpKey($mockKey);
        $this->assertEquals(false, $return);
    }

    public function testGetOrderFromOpkeyReturnsFalseWhenNoOrderIdProvided()
    {
        $mockKey = base64_encode(serialize([
            'p' => 23,
            'c' => '2016-09-05 17:12:30'
        ]));

        $return = $this->subject->getOrderFromOpKey($mockKey);
        $this->assertEquals(false, $return);
    }

    public function testGetOrderFromOpkeyReturnsFalseWhenOrderIdProvidedNotInt()
    {
        $mockKey = base64_encode(serialize([
            'o' => 'test',
            'p' => 23,
            'c' => '2016-09-05 17:12:30'
        ]));

        $return = $this->subject->getOrderFromOpKey($mockKey);
        $this->assertEquals(false, $return);
    }

    public function testGetOrderFromOpkeyReturnsFalseWhenNoCustomerIdProvided()
    {
        $mockKey = base64_encode(serialize([
            'o' => 23,
            'c' => '2016-09-05 17:12:30'
        ]));

        $return = $this->subject->getOrderFromOpKey($mockKey);
        $this->assertEquals(false, $return);
    }

    public function testGetOrderFromOpkeyReturnsFalseWhenCustomerIdProvidedNotInt()
    {
        $mockKey = base64_encode(serialize([
            'o' => 23,
            'p' => 'test',
            'c' => '2016-09-05 17:12:30'
        ]));

        $return = $this->subject->getOrderFromOpKey($mockKey);
        $this->assertEquals(false, $return);
    }

    public function testGetOrderFromOpkeyReturnsFalseWhenNoCreatedAtProvided()
    {
        $mockKey = base64_encode(serialize([
            'o' => 23,
            'p' => 123
        ]));

        $return = $this->subject->getOrderFromOpKey($mockKey);
        $this->assertEquals(false, $return);
    }

    public function testGetOrderFromOpkeyReturnsFalseWhenCreatedAtProvidedEmpty()
    {
        $mockKey = base64_encode(serialize([
            'o' => 23,
            'p' => 123,
            'c' => ''
        ]));

        $return = $this->subject->getOrderFromOpKey($mockKey);
        $this->assertEquals(false, $return);
    }

    public function dataProviderTestGetOrderFromOpKeyReturnsNullWhenOpKeyInfoIsIncorrect()
    {
        return [
            [null, null, null],
            [1095, null, null],
            [1096, null, null],
            [1095, 560, null],
            [1095, 5601, null],
            [121, 321, null],
            [121, 321, '2016-09-05 17:13:30'],
            [1095, 561, '2016-09-05 17:12:30'],
            [1096, 560, '2016-09-05 17:12:30'],
        ];
    }

    /**
     * @param $orderId
     * @param $customerId
     * @param $createdAt
     * @dataProvider dataProviderTestGetOrderFromOpKeyReturnsNullWhenOpKeyInfoIsIncorrect
     */
    public function testGetOrderFromOpKeyReturnsNullWhenOpKeyInfoIsIncorrect(
        $orderId, $customerId, $createdAt
    ) {
        $mockKey = base64_encode(serialize([
            'o' => 1095,
            'p' =>  560,
            'c' => '2016-09-05 17:12:30'
        ]));

        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getId', 'getCustomerId', 'getCreatedAt'])
            ->getMock();

        $order
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($orderId));

        $order
            ->expects($this->any())
            ->method('getCustomerId')
            ->will($this->returnValue($customerId));

        $order
            ->expects($this->any())
            ->method('getCreatedAt')
            ->will($this->returnValue($createdAt));

        $this
            ->orderFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($order));

        $return = $this->subject->getOrderFromOpKey($mockKey);

        $this->assertNull($return);
    }

    public function testGetOrderFromOpKeyReturnsOrderWhenOpKeyInfoIsCorrect() {
        $orderId = 1095;
        $customerId = 560;
        $createdAt = '2016-09-05 17:12:30';

        $mockKey = base64_encode(serialize([
            'o' => $orderId,
            'p' =>  $customerId,
            'c' => $createdAt
        ]));

        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getId', 'getCustomerId', 'getCreatedAt'])
            ->getMock();

        $order
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($orderId));

        $order
            ->expects($this->any())
            ->method('getCustomerId')
            ->will($this->returnValue($customerId));

        $order
            ->expects($this->any())
            ->method('getCreatedAt')
            ->will($this->returnValue($createdAt));

        $this
            ->orderFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($order));

        $return = $this->subject->getOrderFromOpKey($mockKey);

        $this->assertEquals($order, $return);
    }

    public function dataProviderTestConvertBoletoIdToOrderIncrementIdReturnsExpectedValues()
    {
        return [
            [10, '000000010'],
            ['00001020', '000001020'],
            ['10000030', '1000000030'],
            ['14000030', '14000000030'],
        ];
    }

    /**
     * @param string|int $boletoId
     * @param string $expectedOrderIncrementId
     * @dataProvider dataProviderTestConvertBoletoIdToOrderIncrementIdReturnsExpectedValues
     */
    public function testConvertBoletoIdToOrderIncrementIdReturnsExpectedValues($boletoId, $expectedOrderIncrementId)
    {
        $return = $this->subject->convertBoletoIdToOrderIncrementId($boletoId);
        $this->assertEquals($expectedOrderIncrementId, $return);
    }

    public function dataProviderTestConvertOrderIncrementIdToBoletoIdReturnsExpectedValues()
    {
        return[
            ['000001020', null, '00001020'],
            ['1000000030', '1', '10000030'],
            ['14000000030', '14', '14000030'],
        ];
    }

    /**
     * @param string $orderIncrementId
     * @param string $incrementPrefix
     * @param string $expectedBoletoId
     * @dataProvider dataProviderTestConvertOrderIncrementIdToBoletoIdReturnsExpectedValues
     */
    public function testConvertOrderIncrementIdToBoletoIdReturnsExpectedValues(
        $orderIncrementId, $incrementPrefix, $expectedBoletoId
    ) {
        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIncrementId'])
            ->getMock();

        $order
            ->expects($this->once())
            ->method('getIncrementId')
            ->will($this->returnValue($orderIncrementId));

        $this
            ->subject
            ->expects($this->once())
            ->method('_getOrderSequencePrefix')
            ->with($order)
            ->will($this->returnValue($incrementPrefix));

        $return = $this->subject->convertOrderIncrementIdToBoletoId($order);
        $this->assertEquals($expectedBoletoId, $return);
    }
}

