<?php

namespace Gabrielqs\Boleto\Test\Unit\Model\Remittance\File;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Magento\Framework\Api\SearchCriteria;
use \Magento\Sales\Model\Order as SalesOrder;
use \Magento\Sales\Model\OrderRepository as SalesOrderRepository;
use \Magento\Framework\Filesystem;
use \Magento\Framework\DataObject;
use \Magento\Framework\App\Filesystem\DirectoryList;
use \Magento\Framework\Filesystem\Directory\Write as DirectoryWrite;
use \Magento\Framework\Filesystem\File\Write as FileWrite;
use \Gabrielqs\Boleto\Model\Remittance\File as Subject;
use \Gabrielqs\Boleto\Model\Remittance\File\EventRepository;
use \Gabrielqs\Boleto\Model\Remittance\File\EventFactory;
use \Gabrielqs\Boleto\Model\Remittance\File\Event;
use \Gabrielqs\Boleto\Model\Remittance\File\OrderRepository;
use \Gabrielqs\Boleto\Model\Remittance\File\OrderFactory;
use \Gabrielqs\Boleto\Model\Remittance\File\Order;
use \Gabrielqs\Boleto\Api\Data\RemittanceFileEventInterface;
use \Gabrielqs\Boleto\Api\Data\RemittanceFileOrderInterface;

/**
 * Unit Testcase
 */
class FileTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Event Factory
     * @var EventFactory|null
     */
    protected $eventFactory = null;

    /**
     * Event Repository
     * @var EventRepository|null
     */
    protected $eventRepository = null;

    /**
     * File System
     * @var Filesystem $fileSystem
     */
    protected $fileSystem = null;

    /**
     * @var String
     */
    protected $className = null;

    /**
     * @var ObjectManager
     */
    protected $objectManager = null;

    /**
     * Order Factory
     * @var OrderFactory|null
     */
    protected $orderFactory = null;

    /**
     * Order Repository
     * @var OrderRepository|null
     */
    protected $orderRepository = null;

    /**
     * Sales Order Factory
     * @var SalesOrderRepository|null
     */
    protected $salesOrderRepository = null;

    /**
     * @var SearchCriteria
     */
    protected $searchCriteria = null;

    /**
     * Search Criteria Builder
     * @var SearchCriteriaBuilder|null
     */
    protected $searchCriteriaBuilder = null;

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

        $this->salesOrderRepository = $this->getMockBuilder(SalesOrderRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'getList'])
            ->getMock();
        $arguments['salesOrderRepository'] = $this->salesOrderRepository;

        $this->orderRepository = $this->getMockBuilder(OrderRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['save', 'getList'])
            ->getMock();
        $arguments['orderRepository'] = $this->orderRepository;

        $this->orderFactory = $this->getMockBuilder(OrderFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $arguments['orderFactory'] = $this->orderFactory;

        $this->eventRepository = $this->getMockBuilder(EventRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['save', 'getList'])
            ->getMock();
        $arguments['eventRepository'] = $this->eventRepository;

        $this->eventFactory = $this->getMockBuilder(EventFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $arguments['eventFactory'] = $this->eventFactory;

        $this->searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['addFilter', 'create'])
            ->getMock();
        $arguments['searchCriteriaBuilder'] = $this->searchCriteriaBuilder;

        $this->searchCriteria = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $arguments['searchCriteria'] = $this->searchCriteria;

        $this->fileSystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDirectoryWrite', 'getAbsolutePath'])
            ->getMock();
        $arguments['fileSystem'] = $this->fileSystem;

        return $arguments;
    }

    public function testGetCreationTimeGetsTheRightKey()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('getData')
            ->with(Subject::CREATION_TIME);
        $this->subject->getCreationTime();
    }

    public function testSetCreationTimeSetsTheRightKey()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('setData')
            ->with(Subject::CREATION_TIME, 'foobar');
        $this->subject->setCreationTime('foobar');
    }

    public function testGetIdGetsTheRightKey()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('getData')
            ->with(Subject::REMITTANCE_FILE_ID);
        $this->subject->getId();
    }

    public function testSetIdSetsTheRightKey()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('setData')
            ->with(Subject::REMITTANCE_FILE_ID, 'foobar');
        $this->subject->setId('foobar');
    }

    public function testGetIdentitiesRemittanceTheRightValue()
    {
        $expectedReturn = [Subject::CACHE_TAG . '_2'];
        $this
            ->originalSubject
            ->setId(2);
        $return = $this->originalSubject->getIdentities();
        $this->assertEquals($expectedReturn, $return);
    }

    public function testGetNameGetsTheRightKey()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('getData')
            ->with(Subject::NAME);
        $this->subject->getName();
    }

    public function testSetNameSetsTheRightKey()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('setData')
            ->with(Subject::NAME, 'foobar');
        $this->subject->setName('foobar');
    }

    public function testGetStatusGetsTheRightKey()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('getData')
            ->with(Subject::STATUS);
        $this->subject->getStatus();
    }

    public function testSetStatusSetsTheRightKey()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('setData')
            ->with(Subject::STATUS, 'foobar');
        $this->subject->setStatus('foobar');
    }

    public function testGetUpdateTimeGetsTheRightKey()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('getData')
            ->with(Subject::UPDATE_TIME);
        $this->subject->getUpdateTime();
    }

    public function testSetUpdateTimeSetsTheRightKey()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('setData')
            ->with(Subject::UPDATE_TIME, 'foobar');
        $this->subject->setUpdateTime('foobar');
    }

    public function testCreateNewEventCallsFactoryAndRepositorySubsequently()
    {
        $description = 'File Imported.';

        $this
            ->subject
            ->expects($this->any())
            ->method('getData')
            ->with(Subject::REMITTANCE_FILE_ID)
            ->will($this->returnValue(393));

        $event = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['setRemittanceFileId', 'setDescription'])
            ->getMock();

        $this
            ->eventFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($event));

        $event
            ->expects($this->once())
            ->method('setRemittanceFileId')
            ->with(393)
            ->will($this->returnValue($event));

        $event
            ->expects($this->once())
            ->method('setDescription')
            ->with($description)
            ->will($this->returnValue($event));

        $this
            ->eventRepository
            ->expects($this->once())
            ->method('save')
            ->with($event)
            ->will($this->returnValue($event));

        $return = $this->subject->createNewEvent($description);

        $this->assertEquals($event, $return);
    }

    public function testCreateNewOrderByIdCallsFactoryAndRepositorySubsequently()
    {
        $orderId = 123;

        $this
            ->subject
            ->expects($this->any())
            ->method('getData')
            ->with(Subject::REMITTANCE_FILE_ID)
            ->will($this->returnValue(38383));

        $this
            ->searchCriteriaBuilder
            ->expects($this->once())
            ->method('addFilter')
            ->with(SalesOrder::ENTITY_ID, $orderId, 'eq')
            ->will($this->returnValue($this->searchCriteriaBuilder));

        $this
            ->searchCriteriaBuilder
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->searchCriteria));

        $salesOrder = $this->getMockBuilder(SalesOrder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $salesOrder
            ->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(39271));

        $collection = $this->getMockBuilder(DataObject::class)
            ->setMethods(['getTotalCount', 'getFirstItem'])
            ->disableOriginalConstructor()
            ->getMock();

        $collection
            ->expects($this->once())
            ->method('getTotalCount')
            ->will($this->returnValue(1));

        $collection
            ->expects($this->once())
            ->method('getFirstItem')
            ->will($this->returnValue($salesOrder));

        $this
            ->salesOrderRepository
            ->expects($this->once())
            ->method('getList')
            ->with($this->searchCriteria)
            ->will($this->returnValue($collection));


        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['setOrderId', 'setRemittanceFileId'])
            ->getMock();

        $this
            ->orderFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($order));

        $order
            ->expects($this->once())
            ->method('setRemittanceFileId')
            ->with(38383)
            ->will($this->returnValue($order));

        $order
            ->expects($this->once())
            ->method('setOrderId')
            ->with(39271)
            ->will($this->returnValue($order));

        $this
            ->orderRepository
            ->expects($this->once())
            ->method('save')
            ->with($order)
            ->will($this->returnValue($order));

        $return = $this->subject->createNewOrderById($orderId);

        $this->assertEquals($order, $return);
    }

    public function testCreateNewOrderByIdDoesntInsertWhenOrderNotFound()
    {
        $orderId = 123;

        $this
            ->subject
            ->expects($this->any())
            ->method('getData')
            ->with(Subject::REMITTANCE_FILE_ID)
            ->will($this->returnValue(38383));

        $this
            ->searchCriteriaBuilder
            ->expects($this->once())
            ->method('addFilter')
            ->with(SalesOrder::ENTITY_ID, $orderId, 'eq')
            ->will($this->returnValue($this->searchCriteriaBuilder));

        $this
            ->searchCriteriaBuilder
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->searchCriteria));

        $collection = $this->getMockBuilder(DataObject::class)
            ->setMethods(['getTotalCount', 'getFirstItem'])
            ->disableOriginalConstructor()
            ->getMock();

        $collection
            ->expects($this->once())
            ->method('getTotalCount')
            ->will($this->returnValue(0));

        $collection
            ->expects($this->never())
            ->method('getFirstItem');

        $this
            ->salesOrderRepository
            ->expects($this->once())
            ->method('getList')
            ->with($this->searchCriteria)
            ->will($this->returnValue($collection));

        $this
            ->orderFactory
            ->expects($this->never())
            ->method('create');

        $this
            ->orderRepository
            ->expects($this->never())
            ->method('save');

        $return = $this->subject->createNewOrderById($orderId);

        $this->assertNull($return);
    }

    public function testGetContentsUsesTheFilePath()
    {
        $subject = $this->getMockBuilder(Subject::class)
            ->setMethods(['getPath'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject
            ->expects($this->once())
            ->method('getPath')
            ->will($this->returnValue('/dev/null'));
        $subject->getContents();
    }

    public function testGetEventCollectionQueriesRepositoryOnlyOnce()
    {
        $this
            ->subject
            ->expects($this->any())
            ->method('getData')
            ->with(Subject::REMITTANCE_FILE_ID)
            ->will($this->returnValue(3432));

        $this
            ->searchCriteriaBuilder
            ->expects($this->once())
            ->method('addFilter')
            ->with(RemittanceFileEventInterface::REMITTANCE_FILE_ID, 3432, 'eq')
            ->will($this->returnValue($this->searchCriteriaBuilder));

        $this
            ->searchCriteriaBuilder
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->searchCriteria));

        $collection = $this->getMockBuilder(DataObject::class)
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $this
            ->eventRepository
            ->expects($this->once())
            ->method('getList')
            ->with($this->searchCriteria)
            ->will($this->returnValue($collection));

        $returnA = $this->subject->getEventCollection();
        $returnB = $this->subject->getEventCollection();

        $this->assertEquals($returnA, $returnB);
        $this->assertEquals($collection, $returnB);
    }

    public function testGetOrderCollectionQueriesRepositoryOnlyOnce()
    {
        $this
            ->subject
            ->expects($this->any())
            ->method('getData')
            ->with(Subject::REMITTANCE_FILE_ID)
            ->will($this->returnValue(3432));

        $this
            ->searchCriteriaBuilder
            ->expects($this->once())
            ->method('addFilter')
            ->with(RemittanceFileOrderInterface::REMITTANCE_FILE_ID, 3432, 'eq')
            ->will($this->returnValue($this->searchCriteriaBuilder));

        $this
            ->searchCriteriaBuilder
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->searchCriteria));

        $collection = $this->getMockBuilder(DataObject::class)
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $this
            ->orderRepository
            ->expects($this->once())
            ->method('getList')
            ->with($this->searchCriteria)
            ->will($this->returnValue($collection));

        $returnA = $this->subject->getOrderCollection();
        $returnB = $this->subject->getOrderCollection();

        $this->assertEquals($returnA, $returnB);
        $this->assertEquals($collection, $returnB);
    }

    public function testGetStoragePathGetsItFromFileSystemWithWritePermissionsAndRightFolderName()
    {
        $this
            ->fileSystem
            ->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::VAR_DIR)
            ->will($this->returnValue($this->fileSystem));

        $this
            ->fileSystem
            ->expects($this->once())
            ->method('getAbsolutePath')
            ->with('/boletoremittancefiles/')
            ->will($this->returnValue('/dev/null'));

        $return = $this->subject->getStoragePath();

        $this->assertEquals('/dev/null', $return);
    }


    public function testPathUsesTheRightMethodsToBuildItAndRemittanceTheValueCorrectly()
    {
        $subject = $this->getMockBuilder(Subject::class)
            ->setMethods(['getStoragePath', 'getName'])
            ->disableOriginalConstructor()
            ->getMock();

        $subject
            ->expects($this->once())
            ->method('getStoragePath')
            ->will($this->returnValue('/dev/null'));
        $subject
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('lala.txt'));
        $path = $subject->getPath();

        $this->assertEquals('/dev/null/lala.txt', $path);
    }

    public function testeSaveFileToFileSystemUsesMagentoFrameworkModelsToSaveFile()
    {
        $directoryWrite = $this->getMockBuilder(DirectoryWrite::class)
            ->disableOriginalConstructor()
            ->setMethods(['openFile'])
            ->getMock();

        $this
            ->fileSystem
            ->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::VAR_DIR)
            ->willReturn($directoryWrite);

        $fileWrite = $this->getMockBuilder(FileWrite::class)
            ->disableOriginalConstructor()
            ->setMethods(['lock', 'write', 'unlock', 'close'])
            ->getMock();

        $this
            ->subject
            ->expects($this->once())
            ->method('getData')
            ->with(Subject::NAME)
            ->willReturn('RM20160919.REM');

        $directoryWrite
            ->expects($this->once())
            ->method('openFile')
            ->with('/boletoremittancefiles/RM20160919.REM', 'w')
            ->willReturn($fileWrite);

        $fileWrite
            ->expects($this->once())
            ->method('lock');
        $fileWrite
            ->expects($this->once())
            ->method('write')
            ->with('Foo Bar Content Baz');
        $fileWrite
            ->expects($this->once())
            ->method('unlock');
        $fileWrite
            ->expects($this->once())
            ->method('close');

        $this->subject->saveFileToFileSystem('Foo Bar Content Baz');

    }
}