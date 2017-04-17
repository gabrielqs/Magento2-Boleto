<?php

namespace Gabrielqs\Boleto\Test\Unit\Block\Boleto;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Magento\Framework\DataObject;
use \Magento\Sales\Model\Order;
use \Gabrielqs\Boleto\Block\Boleto\Info as Subject;
use \Gabrielqs\Boleto\Helper\Boleto\Data as BoletoHelper;

/**
 * Unit Testcase
 */
class IntoTest extends \PHPUnit_Framework_TestCase
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
            ->setMethods(['getInfo'])
            ->getMock();

        $this->originalSubject = $this
            ->objectManager
            ->getObject($this->className, $arguments);
    }

    protected function getConstructorArguments()
    {
        $arguments = $this->objectManager->getConstructArguments($this->className);

        $this->_boletoHelper = $this->getMockBuilder(BoletoHelper::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPrintBoletoUrl'])
            ->getMock();
        $arguments['boletoHelper'] = $this->_boletoHelper;

        return $arguments;
    }

    public function testGetPrintUrlGetsItFromHelper()
    {

        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $info = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOrder'])
            ->getMock();
        $info
            ->expects($this->once())
            ->method('getOrder')
            ->will($this->returnValue($order));
        $this
            ->subject
            ->expects($this->once())
            ->method('getInfo')
            ->will($this->returnValue($info));

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