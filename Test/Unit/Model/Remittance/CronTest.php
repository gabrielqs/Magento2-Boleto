<?php

namespace Gabrielqs\Boleto\Test\Unit\Model\Remittance;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use \Magento\Framework\Stdlib\DateTime\DateTime;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Magento\Sales\Model\Order as SalesOrder;
use \Magento\Sales\Model\ResourceModel\Order\Collection as SalesOrderCollection;
use \Magento\Sales\Model\ResourceModel\Order\CollectionFactory as SalesOrderCollectionFactory;
use \Gabrielqs\Boleto\Helper\Remittance\Generator as RemittanceFileGenerator;
use \Gabrielqs\Boleto\Model\Remittance\FileRepository as RemittanceFileRepository;
use \Gabrielqs\Boleto\Model\Remittance\FileFactory as RemittanceFileFactory;
use \Gabrielqs\Boleto\Model\Remittance\File as RemittanceFile;
use \Gabrielqs\Boleto\Model\Remittance\Cron as Subject;
use \Gabrielqs\Boleto\Helper\Boleto\Data as BoletoHelper;


/**
 * Unit Testcase
 */
class CronTest extends \PHPUnit_Framework_TestCase
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
     * @var BoletoHelper $_boletoHelper
     */
    protected $boletoHelper = null;

    /**
     * Date Time Factory
     * @var DateTimeFactory $_dateFactory
     */
    protected $dateFactory = null;

    /**
     * Search Criteria Builder
     * @var SearchCriteriaBuilder|null
     */
    protected $searchCriteriaBuilder = null;

    /**
     * Order collection factory
     * @var SalesOrderCollectionFactory|null
     */
    protected $salesOrderCollectionFactory = null;

    /**
     * Remittance File Factory
     * @var RemittanceFileFactory|null
     */
    protected $remittanceFileFactory = null;

    /**
     * Remittance File Generator
     * @var RemittanceFileGenerator|null
     */
    protected $remittanceFileGenerator = null;

    /**
     * Remittance File Repository
     * @var RemittanceFileRepository|null
     */
    protected $remittanceFileRepository = null;

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

        $this->salesOrderCollectionFactory = $this->getMockBuilder(SalesOrderCollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $arguments['salesOrderCollectionFactory'] = $this->salesOrderCollectionFactory;

        $this->remittanceFileGenerator = $this->getMockBuilder(RemittanceFileGenerator::class)
            ->disableOriginalConstructor()
            ->setMethods(['generateFileName', 'generateFileContent'])
            ->getMock();
        $arguments['remittanceFileGenerator'] = $this->remittanceFileGenerator;

        $this->remittanceFileRepository = $this->getMockBuilder(RemittanceFileRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();
        $arguments['remittanceFileRepository'] = $this->remittanceFileRepository;

        $this->remittanceFileFactory = $this->getMockBuilder(RemittanceFileFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $arguments['remittanceFileFactory'] = $this->remittanceFileFactory;

        $this->searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $arguments['searchCriteriaBuilder'] = $this->searchCriteriaBuilder;

        $this->dateFactory = $this->getMockBuilder(DateTimeFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $arguments['dateFactory'] = $this->dateFactory;

        $this->boletoHelper = $this->getMockBuilder(BoletoHelper::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $arguments['boletoHelper'] = $this->boletoHelper;

        return $arguments;
    }

    public function testProcessOrdersDoesntCreateFileWhenNoOrderFound()
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

        $ordersCollection = $this->getMockBuilder(SalesOrderCollection::class)
            ->disableOriginalConstructor()
            ->setMethods(['join', 'addFieldToFilter', 'count'])
            ->getMock();
        $this
            ->salesOrderCollectionFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($ordersCollection);

        $ordersCollection
            ->expects($this->once())
            ->method('join', 'count')
            ->with(
                ['payment' => 'sales_order_payment'],
                'main_table.entity_id=payment.parent_id',
                ['payment_method' => 'payment.method']
            )
            ->willReturnSelf();

        $date
            ->expects($this->exactly(2))
            ->method('date')
            ->withConsecutive(
                ['Y-m-d h:i:s', '-1 days'],
                ['Y-m-d h:i:s', null]
            )->willReturnOnConsecutiveCalls(
                '2016-09-18 22:00:00',
                '2016-09-19 22:00:00'
            );

        $ordersCollection
            ->expects($this->exactly(2))
            ->method('addFieldToFilter')
            ->withConsecutive(
                ['payment.method', ['eq' => 'boleto_boleto']],
                [SalesOrder::CREATED_AT, [
                    'from' => '2016-09-18 22:00:00',
                    'to' => '2016-09-19 22:00:00'
                ]]
            )
            ->willReturnSelf();

        $ordersCollection
            ->expects($this->once())
            ->method('count')
            ->willReturn(0);

        $this
            ->remittanceFileFactory
            ->expects($this->never())
            ->method('create');

        $this->subject->processOrders();
    }

    public function testProcessOrdersSuccessfullyCreatesFileAndChangesStatusWhenOrdersFound()
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

        $ordersCollection = $this->getMockBuilder(SalesOrderCollection::class)
            ->disableOriginalConstructor()
            ->setMethods(['join', 'addFieldToFilter', 'count', 'getItems'])
            ->getMock();
        $this
            ->salesOrderCollectionFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($ordersCollection);

        $ordersCollection
            ->expects($this->once())
            ->method('join', 'count')
            ->with(
                ['payment' => 'sales_order_payment'],
                'main_table.entity_id=payment.parent_id',
                ['payment_method' => 'payment.method']
            )
            ->willReturnSelf();

        $date
            ->expects($this->exactly(2))
            ->method('date')
            ->withConsecutive(
                ['Y-m-d h:i:s', '-1 days'],
                ['Y-m-d h:i:s', null]
            )->willReturnOnConsecutiveCalls(
                '2016-09-18 22:00:00',
                '2016-09-19 22:00:00'
            );

        $ordersCollection
            ->expects($this->exactly(2))
            ->method('addFieldToFilter')
            ->withConsecutive(
                ['payment.method', ['eq' => 'boleto_boleto']],
                [SalesOrder::CREATED_AT, [
                    'from' => '2016-09-18 22:00:00',
                    'to' => '2016-09-19 22:00:00'
                ]]
            )
            ->willReturnSelf();

        $ordersCollection
            ->expects($this->once())
            ->method('count')
            ->willReturn(1);

        $file = $this->getMockBuilder(RemittanceFile::class)
            ->disableOriginalConstructor()
            ->setMethods(['setName', 'setStatus', 'saveFileToFileSystem', 'createNewOrderById', 'createNewEvent'])
            ->getMock();
        $this
            ->remittanceFileFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($file);

        $this
            ->remittanceFileGenerator
            ->expects($this->once())
            ->method('generateFileName')
            ->willReturn('RM20160919.REM');

        $file
            ->expects($this->once())
            ->method('setName')
            ->with('RM20160919.REM')
            ->willReturnSelf();

        $file
            ->expects($this->exactly(2))
            ->method('setStatus')
            ->withConsecutive(
                [RemittanceFile::STATUS_NEW],
                [RemittanceFile::STATUS_SUCCESS]
            )->willReturnSelf();

        $this
            ->remittanceFileRepository
            ->expects($this->exactly(2))
            ->method('save')
            ->with($file);

        $this
            ->remittanceFileGenerator
            ->expects($this->once())
            ->method('generateFileContent')
            ->with($ordersCollection)
            ->willReturn('Foo Bar Content');

        $file
            ->expects($this->once())
            ->method('saveFileToFileSystem')
            ->with('Foo Bar Content');

        $orderA = $this->getMockBuilder(SalesOrder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();
        $orderA
            ->expects($this->once())
            ->method('getId')
            ->willReturn(234);

        $orderB = $this->getMockBuilder(SalesOrder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();
        $orderB
            ->expects($this->once())
            ->method('getId')
            ->willReturn(248);

        $ordersCollection
            ->expects($this->once())
            ->method('getItems')
            ->willReturn([$orderA, $orderB]);

        $file
            ->expects($this->exactly(2))
            ->method('createNewOrderById')
            ->withConsecutive(
                [234],
                [248]
            );

        $file
            ->expects($this->once())
            ->method('createNewEvent')
            ->with(__('Remittance file created.'));

        $this->subject->processOrders();
    }

    public function testProcessOrdersWritesExceptionEventWhenExceptionThrown()
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

        $ordersCollection = $this->getMockBuilder(SalesOrderCollection::class)
            ->disableOriginalConstructor()
            ->setMethods(['join', 'addFieldToFilter', 'count', 'getItems'])
            ->getMock();
        $this
            ->salesOrderCollectionFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($ordersCollection);

        $ordersCollection
            ->expects($this->once())
            ->method('join', 'count')
            ->with(
                ['payment' => 'sales_order_payment'],
                'main_table.entity_id=payment.parent_id',
                ['payment_method' => 'payment.method']
            )
            ->willReturnSelf();

        $date
            ->expects($this->exactly(2))
            ->method('date')
            ->withConsecutive(
                ['Y-m-d h:i:s', '-1 days'],
                ['Y-m-d h:i:s', null]
            )->willReturnOnConsecutiveCalls(
                '2016-09-18 22:00:00',
                '2016-09-19 22:00:00'
            );

        $ordersCollection
            ->expects($this->exactly(2))
            ->method('addFieldToFilter')
            ->withConsecutive(
                ['payment.method', ['eq' => 'boleto_boleto']],
                [SalesOrder::CREATED_AT, [
                    'from' => '2016-09-18 22:00:00',
                    'to' => '2016-09-19 22:00:00'
                ]]
            )
            ->willReturnSelf();

        $ordersCollection
            ->expects($this->once())
            ->method('count')
            ->willReturn(1);

        $file = $this->getMockBuilder(RemittanceFile::class)
            ->disableOriginalConstructor()
            ->setMethods(['setName', 'setStatus', 'saveFileToFileSystem', 'createNewOrderById', 'createNewEvent'])
            ->getMock();
        $this
            ->remittanceFileFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($file);

        $this
            ->remittanceFileGenerator
            ->expects($this->once())
            ->method('generateFileName')
            ->willReturn('RM20160919.REM');

        $file
            ->expects($this->once())
            ->method('setName')
            ->with('RM20160919.REM')
            ->willReturnSelf();

        $file
            ->expects($this->exactly(2))
            ->method('setStatus')
            ->withConsecutive(
                [RemittanceFile::STATUS_NEW],
                [RemittanceFile::STATUS_ERROR]
            )->willReturnSelf();

        $this
            ->remittanceFileRepository
            ->expects($this->exactly(2))
            ->method('save')
            ->with($file);

        $this
            ->remittanceFileGenerator
            ->expects($this->once())
            ->method('generateFileContent')
            ->with($ordersCollection)
            ->willReturn('Foo Bar Content');

        $file
            ->expects($this->once())
            ->method('saveFileToFileSystem')
            ->with('Foo Bar Content')
            ->willThrowException(new \Exception('Foo Bar Exception'));

        $orderA = $this->getMockBuilder(SalesOrder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();
        $orderA
            ->expects($this->never())
            ->method('getId')
            ->willReturn(234);

        $orderB = $this->getMockBuilder(SalesOrder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();
        $orderB
            ->expects($this->never())
            ->method('getId')
            ->willReturn(248);

        $ordersCollection
            ->expects($this->never())
            ->method('getItems');

        $file
            ->expects($this->never())
            ->method('createNewOrderById');

        $file
            ->expects($this->exactly(2))
            ->method('createNewEvent')
            ->withConsecutive(
                [__('An error happened while creating the remittance file.')],
                ['Foo Bar Exception']
            );

        $this->subject->processOrders();
    }

}