<?php

namespace Gabrielqs\Boleto\Test\Unit\Model;

use \Magento\Framework\Exception\LocalizedException;
use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Magento\Framework\DataObject;
use \Magento\Sales\Model\Order;
use \Gabrielqs\Boleto\Model\Boleto as Subject;
use \Gabrielqs\Boleto\Helper\Boleto\Data as BoletoHelper;
use \Gabrielqs\Boleto\Helper\Boleto\Generator as BoletoGenerator;
use \Gabrielqs\Boleto\Model\Boleto\Api;

/**
 * Boleto Test Case
 */
class BoletoTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Api
     */
    protected $api = null;

    /**
     * @var string
     */
    protected $className = null;

    /**
     * @var DataObject
     */
    protected $dataObject = null;

    /**
     * @var ObjectManager
     */
    protected $objectManager = null;

    /**
     * @var Api
     */
    protected $originalSubject = null;

    /**
     * @var BoletoGenerator
     */
    protected $boletoGenerator = null;

    /**
     * @var BoletoHelper
     */
    protected $boletoHelper = null;

    /**
     * @var Api
     */
    protected $subject = null;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->className = Subject::class;

        $this->subject = $this
            ->getMockBuilder($this->className)
            ->setMethods(['getInfoInstance', 'getConfigData'])
            ->setConstructorArgs($this->getConstructorArguments())
            ->getMock();

        $this->dataObject = $this
            ->getMockBuilder('\Magento\Framework\DataObject')
            ->setMethods(['getAdditionalData'])
            ->getMock();

        $this->originalSubject = $this->objectManager->getObject($this->className);
    }

    protected function getConstructorArguments()
    {
        $arguments = $this->objectManager->getConstructArguments($this->className);

        $this->boletoHelper = $this
            ->getMockBuilder(BoletoHelper::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIntegrationToken', 'getMerchantEmail', 'isTest'])
            ->getMock();
        $arguments['boletoHelper'] = $this->boletoHelper;

        $this->boletoGenerator = $this
            ->getMockBuilder(BoletoGenerator::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBoletoHtml'])
            ->getMock();
        $arguments['boletoGenerator'] = $this->boletoGenerator;

        return $arguments;
    }


    public function testCanUseForCurrencySupported()
    {
        $this->assertTrue($this->subject->canUseForCurrency('BRL'));
    }

    public function testCanUseForCurrencyUnsupported()
    {
        $this->assertNotTrue($this->subject->canUseForCurrency('USD'));
    }


    public function testIsAvailableShouldReturnFalseWhenMethodIsNotActive()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('getConfigData')
            ->with('active')
            ->will($this->returnValue(false));

        $this->assertNotTrue($this->subject->isAvailable());
    }

    public function testIsAvailableShouldReturnFalseWhenNoQuoteAvailable()
    {
        $this
            ->subject
            ->expects($this->any())
            ->method('getConfigData')
            ->with('active')
            ->will($this->returnValue(true));

        $quote = null;

        $this->assertNotTrue($this->subject->isAvailable($quote));
    }

    public function testIsAvailableShouldReturnFalseWhenGrandTotalIsLessThanMinimum()
    {
        $this
            ->subject
            ->expects($this->exactly(2))
            ->method('getConfigData')
            ->withConsecutive(
                ['active', null],
                ['min_order_total', null]
            )
            ->willReturnOnConsecutiveCalls(
                $this->returnValue(true),
                $this->returnValue(50)
            );

        $quote = $this
            ->getMockBuilder('\Magento\Quote\Model\Quote')
            ->disableOriginalConstructor()
            ->setMethods(['getBaseGrandTotal'])
            ->getMock();
        $quote
            ->expects($this->once())
            ->method('getBaseGrandTotal')
            ->will($this->returnValue(20));

        $this->assertNotTrue($this->subject->isAvailable($quote));
    }

    public function testIsAvailableShouldReturnFalseWhenGrandTotalIsGreaterThanMaximumAndMaximumIsSet()
    {
        $this
            ->subject
            ->expects($this->exactly(4))
            ->method('getConfigData')
            ->withConsecutive(
                ['active', null],
                ['min_order_total', null],
                ['max_order_total', null],
                ['max_order_total', null]
            )
            ->willReturnOnConsecutiveCalls(
                $this->returnValue(true),
                $this->returnValue(5),
                $this->returnValue(10000),
                $this->returnValue(10000)
            );

        $quote = $this
            ->getMockBuilder('\Magento\Quote\Model\Quote')
            ->disableOriginalConstructor()
            ->setMethods(['getBaseGrandTotal'])
            ->getMock();
        $quote
            ->expects($this->exactly(2))
            ->method('getBaseGrandTotal')
            ->will($this->returnValue(100000));

        $this->assertNotTrue($this->subject->isAvailable($quote));
    }

    public function testOrderGetsHtmlFromHelperAndSetsItToPaymentObject()
    {

        $amount = 393.19;

        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $payment = $this
            ->getMock('Magento\Sales\Model\Order\Payment', [
                'getOrder',
                'setAmount',
                'setStatus',
                'setIsTransactionPending',
                'setAdditionalInformation'
            ], [], '', false);

        $payment
            ->expects($this->once())
            ->method('getOrder')
            ->will($this->returnValue($order));
        $payment
            ->expects($this->once())
            ->method('setAmount')
            ->with($amount)
            ->will($this->returnValue($payment));
        $payment
            ->expects($this->once())
            ->method('setStatus')
            ->with(Subject::STATUS_SUCCESS)
            ->will($this->returnValue($payment));
        $payment
            ->expects($this->once())
            ->method('setIsTransactionPending')
            ->with(false)
            ->will($this->returnValue($payment));
        $payment
            ->expects($this->once())
            ->method('setAdditionalInformation')
            ->with('boleto_html', '<boleto><html>Boleto</html></boleto>')
            ->will($this->returnValue($payment));

        $this
            ->boletoGenerator
            ->expects($this->once())
            ->method('getBoletoHtml')
            ->with($order)
            ->will($this->returnValue('<boleto><html>Boleto</html></boleto>'));


        $this->subject->order($payment, $amount);
    }

}