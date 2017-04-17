<?php

namespace Gabrielqs\Boleto\Test\Unit\Model\Returns\File\Order;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order;
use \Magento\Sales\Model\OrderRepository as SalesOrderRepository;
use \Gabrielqs\Boleto\Model\Returns\File\Order as Subject;

/**
 * Unit Testcase
 */
class OrderTest extends \PHPUnit_Framework_TestCase
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
     * Sales Order Factory
     * @var SalesOrderRepository|null
     */
    protected $orderRepository = null;

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
            ->setMethods(['getData', 'setData'])
            ->getMock();

        $this->originalSubject = $this
            ->objectManager
            ->getObject($this->className, $arguments);
    }

    protected function getConstructorArguments()
    {
        $arguments = $this->objectManager->getConstructArguments($this->className);

        $this->orderRepository = $this->getMockBuilder(SalesOrderRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
        $arguments['orderRepository'] = $this->orderRepository;

        return $arguments;
    }

    public function testGetIdGetsTheRightKey()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('getData')
            ->with(Subject::RETURNS_FILE_ORDER_ID);
        $this->subject->getId();
    }

    public function testSetIdSetsTheRightKey()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('setData')
            ->with(Subject::RETURNS_FILE_ORDER_ID, 'foobar');
        $this->subject->setId('foobar');
    }

    public function testGetOrderIdGetsTheRightKey()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('getData')
            ->with(Subject::ORDER_ID);
        $this->subject->getOrderId();
    }

    public function testSetOrderIdSetsTheRightKey()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('setData')
            ->with(Subject::ORDER_ID, 'foobar');
        $this->subject->setOrderId('foobar');
    }

    public function testGetIdentitiesReturnsTheRightValue()
    {
        $expectedReturn = [Subject::CACHE_TAG . '_2'];
        $this
            ->originalSubject
            ->setId(2);
        $return = $this->originalSubject->getIdentities();
        $this->assertEquals($expectedReturn, $return);
    }

    public function testGetReturnsFileIdGetsTheRightKey()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('getData')
            ->with(Subject::RETURNS_FILE_ID);
        $this->subject->getReturnsFileId();
    }

    public function testSetReturnsFileIdSetsTheRightKey()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('setData')
            ->with(Subject::RETURNS_FILE_ID, 'foobar');
        $this->subject->setReturnsFileId('foobar');
    }

    public function testGetOrderGetsItFromRepositoryWithRightValueAndOnlyOnce()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('getData')
            ->with(Subject::ORDER_ID)
            ->will($this->returnValue(392));

        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this
            ->orderRepository
            ->expects($this->once())
            ->method('get')
            ->with(392)
            ->will($this->returnValue($order));

        $this->subject->getOrder();
        $this->subject->getOrder();
    }
}