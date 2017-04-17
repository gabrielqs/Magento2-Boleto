<?php

namespace Gabrielqs\Boleto\Test\Unit\Controller\Boletoprint;

use \Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DataObject;
use \Magento\Framework\Exception\LocalizedException;
use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Magento\Sales\Model\Order;
use \Magento\Sales\Model\Order\Payment;
use \Gabrielqs\Boleto\Controller\Boletoprint\Index as Subject;
use \Gabrielqs\Boleto\Helper\Boleto\Data as BoletoHelper;

/**
 * Unit Testcase
 */
class IndexTest extends \PHPUnit_Framework_TestCase
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
     * Result Factory
     * @var ResultFactory
     */
    protected $_resultFactory;

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
            ->setMethods(['_getOpkeyFromRequest'])
            ->getMock();

        $this->originalSubject = $this
            ->objectManager
            ->getObject($this->className, $arguments);
    }

    protected function getConstructorArguments()
    {
        $arguments = $this->objectManager->getConstructArguments($this->className);


        $this->_resultFactory = $this
            ->getMock(ResultFactory::class, [
                'create',
            ], [], '', false);
        $arguments['rawResultFactory'] = $this->_resultFactory;

        $this->_boletoHelper = $this->getMockBuilder(BoletoHelper::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOrderFromOpKey'])
            ->getMock();
        $arguments['boletoHelper'] = $this->_boletoHelper;

        return $arguments;
    }

    public function testExecuteThrowsExceptionWhenNoOrderFound()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('_getOpkeyFromRequest')
            ->will($this->returnValue('asdfasdf'));

        $this
            ->_boletoHelper
            ->expects($this->once())
            ->method('getOrderFromOpKey')
            ->will($this->returnValue(null));

        $this->setExpectedException(LocalizedException::class);

        $this->subject->execute();
    }

    public function testExecuteThrowsExceptionWhenNoBoletoFoundInPayment()
    {
        $payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAdditionalInformation'])
            ->getMock();

        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPayment'])
            ->getMock();

        $order
            ->expects($this->any())
            ->method('getPayment')
            ->will($this->returnValue($payment));

        $this
            ->subject
            ->expects($this->once())
            ->method('_getOpkeyFromRequest')
            ->will($this->returnValue('asdfasdf'));

        $this
            ->_boletoHelper
            ->expects($this->once())
            ->method('getOrderFromOpKey')
            ->will($this->returnValue($order));

        $payment
            ->expects($this->any())
            ->method('getAdditionalInformation')
            ->with('boleto_html')
            ->will($this->returnValue(null));

        $this->setExpectedException(LocalizedException::class);

        $this->subject->execute();
    }

    public function testExecuteReturnsCorrectRawResultWhenRequestIsCorrect()
    {
        $payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAdditionalInformation'])
            ->getMock();

        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPayment'])
            ->getMock();

        $order
            ->expects($this->any())
            ->method('getPayment')
            ->will($this->returnValue($payment));

        $this
            ->subject
            ->expects($this->once())
            ->method('_getOpkeyFromRequest')
            ->will($this->returnValue('asdfasdf'));

        $this
            ->_boletoHelper
            ->expects($this->once())
            ->method('getOrderFromOpKey')
            ->will($this->returnValue($order));

        $payment
            ->expects($this->any())
            ->method('getAdditionalInformation')
            ->with('boleto_html')
            ->will($this->returnValue('<html><p>Boleto</p></html>'));

        $result = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['setHeader', 'setContents'])
            ->getMock();

        $result
            ->expects($this->once())
            ->method('setHeader')
            ->with($this->equalTo('Content-Type'), $this->equalTo('text/html'));
        $result
            ->expects($this->once())
            ->method('setContents')
            ->with($this->equalTo('<html><p>Boleto</p></html>'));

        $this
            ->_resultFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($result));


        $this->subject->execute();
    }

}