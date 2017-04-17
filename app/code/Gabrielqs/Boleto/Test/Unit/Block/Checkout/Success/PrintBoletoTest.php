<?php

namespace Gabrielqs\Boleto\Test\Unit\Block\Checkout\Success;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Magento\Sales\Model\Order\Payment;
use \Magento\Sales\Model\Order;
use \Gabrielqs\Boleto\Block\Checkout\Success\PrintBoleto as Subject;
use \Gabrielqs\Boleto\Helper\Boleto\Data as BoletoHelper;

/**
 * Unit Testcase
 */
class PrintBoletoTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var BoletoHelper
     */
    protected $_boletoHelper = null;

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

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->className = Subject::class;
        $arguments = $this->getConstructorArguments();

        $this->subject = $this
            ->getMockBuilder($this->className)
            ->setConstructorArgs($arguments)
            ->setMethods(['_getOrder'])
            ->getMock();

        $this->originalSubject = $this
            ->objectManager
            ->getObject($this->className, $arguments);
    }

    protected function getConstructorArguments()
    {
        $arguments = $this->objectManager->getConstructArguments($this->className);

        $arguments['data'] = [];

        $this->_boletoHelper = $this->getMockBuilder(BoletoHelper::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMethodCode', 'getPrintBoletoUrl'])
            ->getMock();
        $arguments['boletoHelper'] = $this->_boletoHelper;

        return $arguments;
    }

    public function dataProviderTestIsShowReturnsFalseWhenOrderIsNotLoadedOrNotBoletoPaymentMethod()
    {

        return [
            [null, false],
            ['cielo_redirect', false],
            ['boleto_boleto', true]
        ];
    }

    /**
     * @param $methodCode
     * @param $expectedReturn
     * @dataProvider dataProviderTestIsShowReturnsFalseWhenOrderIsNotLoadedOrNotBoletoPaymentMethod
     */
    public function testIsShowReturnsFalseWhenOrderIsNotLoadedOrNotBoletoPaymentMethod(
        $methodCode, $expectedReturn
    ) {
        $payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMethod'])
            ->getMock();

        $payment
            ->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue($methodCode));

        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPayment'])
            ->getMock();

        $order
            ->expects($this->exactly(2))
            ->method('getPayment')
            ->will($this->returnValue($payment));

        $this
            ->subject
            ->expects($this->once())
            ->method('_getOrder')
            ->will($this->returnValue($order));

        $this->_boletoHelper
            ->expects($this->once())
            ->method('getMethodCode')
            ->will($this->returnValue('boleto_boleto'));

        $return = $this->subject->isShow();

        $this->assertEquals($expectedReturn, $return);
    }

    public function testGetPrintUrlGetsItFromHelper()
    {
        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this
            ->subject
            ->expects($this->once())
            ->method('_getOrder')
            ->will($this->returnValue($order));

        $this
            ->_boletoHelper
            ->expects($this->once())
            ->method('getPrintBoletoUrl')
            ->with($order)
            ->will($this->returnValue('http://google.com/'));

        $return = $this->subject->getPrintUrl();

        $this->assertEquals('http://google.com/', $return);
    }

}