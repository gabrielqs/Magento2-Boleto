<?php

namespace Gabrielqs\Boleto\Test\Unit\Model\Returns;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Magento\Framework\Api\SearchCriteria;
use \Magento\Sales\Model\Order as SalesOrder;
use \Magento\Sales\Model\ResourceModel\Order\Collection as SalesOrderCollection;
use \Magento\Sales\Model\OrderRepository as SalesOrderRepository;
use \Magento\Sales\Model\Service\InvoiceService;
use \Magento\Sales\Model\Order\Invoice;
use \Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use \Gabrielqs\Boleto\Model\Returns\Cron as Subject;
use \Gabrielqs\Boleto\Model\Returns\File as ReturnsFile;
use \Gabrielqs\Boleto\Model\ResourceModel\Returns\File\Collection as ReturnsFileCollection;
use \Gabrielqs\Boleto\Model\Returns\FileRepository as ReturnsFileRepository;
use \Gabrielqs\Boleto\Helper\Returns\Reader as ReturnsFileReader;


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
     * @var ReturnsFile
     */
    protected $fileA = null;

    /**
     * @var ReturnsFile
     */
    protected $fileB = null;

    /**
     * @var ReturnsFile
     */
    protected $fileC = null;

    /**
     * @var ReturnsFile
     */
    protected $fileD = null;

    /**
     * Invoice Sender
     * @var InvoiceSender
     */
    protected $invoiceSender = null;

    /**
     * Invoice Service
     * @var InvoiceService
     */
    protected $invoiceService = null;

    /**
     * @var ObjectManager
     */
    protected $objectManager = null;

    /**
     * @var Subject
     */
    protected $originalSubject = null;

    /**
     * Returns File Collection
     * @var ReturnsFileCollection $returnsFileCollection
     */
    protected $returnsFileCollection = null;

    /**
     * Returns File Reader
     * @var ReturnsFileReader|null
     */
    protected $returnsFileReader = null;

    /**
     * Returns File Repository
     * @var ReturnsFileRepository $returnsFileRepository
     */
    protected $returnsFileRepository = null;

    /**
     * SalesOrder Repository
     * @var SalesOrderRepository|null
     */
    protected $salesOrderRepository = null;

    /**
     * @var SearchCriteria
     */
    protected $searchCriteria = null;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder = null;

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
            ->setMethods(null)
            ->getMock();

        $this->originalSubject = $this
            ->objectManager
            ->getObject($this->className, $arguments);


        $this->fileA = $this->getMockBuilder(ReturnsFile::class)
            ->disableOriginalConstructor()
            ->setMethods(['createNewEvent'])
            ->getMock();

        $this->fileB = $this->getMockBuilder(ReturnsFile::class)
            ->disableOriginalConstructor()
            ->setMethods(['createNewEvent'])
            ->getMock();

        $this->fileC = $this->getMockBuilder(ReturnsFile::class)
            ->disableOriginalConstructor()
            ->setMethods(['createNewEvent'])
            ->getMock();


        $this->fileD = $this->getMockBuilder(ReturnsFile::class)
            ->disableOriginalConstructor()
            ->setMethods(['createNewEvent'])
            ->getMock();

        $this->returnsFileCollection = $this->getMockBuilder(ReturnsFileCollection::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'toArray'])
            ->getMock();

    }

    protected function getConstructorArguments()
    {
        $arguments = $this->objectManager->getConstructArguments($this->className);

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

        $this->returnsFileReader = $this->getMockBuilder(ReturnsFileReader::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOrdersIdsAndValues'])
            ->getMock();
        $arguments['returnsFileReader'] = $this->returnsFileReader;

        $this->returnsFileRepository = $this->getMockBuilder(ReturnsFileRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getList', 'save'])
            ->getMock();
        $arguments['returnsFileRepository'] = $this->returnsFileRepository;

        $this->salesOrderRepository = $this->getMockBuilder(SalesOrderRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getList', 'save'])
            ->getMock();
        $arguments['orderRepository'] = $this->salesOrderRepository;

        $this->invoiceService = $this->getMockBuilder(InvoiceService::class)
            ->disableOriginalConstructor()
            ->setMethods(['prepareInvoice'])
            ->getMock();
        $arguments['invoiceService'] = $this->invoiceService;

        $this->invoiceSender = $this->getMockBuilder(InvoiceSender::class)
            ->disableOriginalConstructor()
            ->setMethods(['send'])
            ->getMock();
        $arguments['invoiceSender'] = $this->invoiceSender;

        return $arguments;
    }

    public function testProcessFiles()
    {
        $this
            ->searchCriteriaBuilder
            ->expects($this->exactly(5))
            ->method('addFilter')
            ->withConsecutive(
                [ReturnsFile::STATUS, ReturnsFile::STATUS_NEW, 'eq'],
                [SalesOrder::INCREMENT_ID, '00024339', 'eq'],
                [SalesOrder::INCREMENT_ID, '00024345', 'eq'],
                [SalesOrder::INCREMENT_ID, '00024378', 'eq'],
                [SalesOrder::INCREMENT_ID, 'nonexistant', 'eq']
                )
            ->willReturnOnConsecutiveCalls(
                $this->returnValue($this->searchCriteriaBuilder),
                $this->returnValue($this->searchCriteriaBuilder),
                $this->returnValue($this->searchCriteriaBuilder),
                $this->returnValue($this->searchCriteriaBuilder),
                $this->returnValue($this->searchCriteriaBuilder)
            );

        $this
            ->searchCriteriaBuilder
            ->expects($this->exactly(5))
            ->method('create')
            ->will($this->returnValue($this->searchCriteria));

        $this
            ->returnsFileRepository
            ->expects($this->once())
            ->method('getList')
            ->with($this->searchCriteria)
            ->willReturn([$this->fileA, $this->fileB, $this->fileC, $this->fileD]);

        $fileAIdAndValue = new \stdClass();
        $fileAIdAndValue->orderId = '00024339';
        $fileAIdAndValue->value = 383.49;

        $fileBIdAndValue = new \stdClass();
        $fileBIdAndValue->orderId = '00024345';
        $fileBIdAndValue->value = 383.49;

        $fileCIdAndValue = new \stdClass();
        $fileCIdAndValue->orderId = '00024378';
        $fileCIdAndValue->value = 383.49;

        $fileDIdAndValue = new \stdClass();
        $fileDIdAndValue->orderId = 'nonexistant';
        $fileDIdAndValue->value = 33232349.39;

        $this
            ->returnsFileReader
            ->expects($this->exactly(4))
            ->method('getOrdersIdsAndValues')
            ->withConsecutive(
                [$this->fileA],
                [$this->fileB],
                [$this->fileC],
                [$this->fileD]
            )
            ->willReturnOnConsecutiveCalls(
                [$fileAIdAndValue], [$fileBIdAndValue], [$fileCIdAndValue], [$fileDIdAndValue]
            );

        $orderA = $this->getMockBuilder(SalesOrder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getGrandTotal'])
            ->getMock();
        $orderA
            ->expects($this->once())
            ->method('getGrandTotal')
            ->will($this->returnValue(383.50));
        $orderCollectionA = $this->getMockBuilder(SalesOrderCollection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTotalCount', 'getFirstItem'])
            ->getMock();
        $orderCollectionA
            ->expects($this->once())
            ->method('getTotalCount')
            ->willReturn(1);
        $orderCollectionA
            ->expects($this->once())
            ->method('getFirstItem')
            ->willReturn($orderA);

        $orderB = $this->getMockBuilder(SalesOrder::class)
            ->disableOriginalConstructor()
            ->setMethods(['canInvoice', 'addStatusHistoryComment',
                'setIsCustomerNotified', 'save', 'getGrandTotal'])
            ->getMock();
        $orderB
            ->expects($this->once())
            ->method('getGrandTotal')
            ->will($this->returnValue(383.40));
        $orderB
            ->expects($this->once())
            ->method('canInvoice')
            ->will($this->returnValue(true));
        $orderCollectionB = $this->getMockBuilder(SalesOrderCollection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTotalCount', 'getFirstItem'])
            ->getMock();
        $orderCollectionB
            ->expects($this->once())
            ->method('getTotalCount')
            ->willReturn(1);
        $orderCollectionB
            ->expects($this->once())
            ->method('getFirstItem')
            ->willReturn($orderB);

        $orderC = $this->getMockBuilder(SalesOrder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderCollectionC = $this->getMockBuilder(SalesOrderCollection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTotalCount', 'getFirstItem'])
            ->getMock();
        $orderCollectionC
            ->expects($this->once())
            ->method('getTotalCount')
            ->willReturn(1);
        $orderCollectionC
            ->expects($this->once())
            ->method('getFirstItem')
            ->willReturn($orderC);


        $orderCollectionD = $this->getMockBuilder(SalesOrderCollection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTotalCount', 'getFirstItem'])
            ->getMock();
        $orderCollectionD
            ->expects($this->once())
            ->method('getTotalCount')
            ->willReturn(0);
        $orderCollectionD
            ->expects($this->never())
            ->method('getFirstItem')
            ->willReturn($orderC);


        $this
            ->salesOrderRepository
            ->expects($this->exactly(4))
            ->method('getList')
            ->withConsecutive(
                $this->searchCriteria,
                $this->searchCriteria,
                $this->searchCriteria,
                $this->searchCriteria
            )
            ->willReturnOnConsecutiveCalls(
                $orderCollectionA,
                $orderCollectionB,
                $orderCollectionC,
                $orderCollectionD
            );

        $invoice = $this->getMockBuilder(Invoice::class)
            ->disableOriginalConstructor()
            ->setMethods(['register', 'capture', 'save'])
            ->getMock();

        $invoice
            ->expects($this->once())
            ->method('register')
            ->will($this->returnValue($invoice));

        $invoice
            ->expects($this->once())
            ->method('save')
            ->will($this->returnValue($invoice));

        $invoice
            ->expects($this->once())
            ->method('capture')
            ->will($this->returnValue($invoice));

        $this
            ->invoiceService
            ->expects($this->once())
            ->method('prepareInvoice')
            ->will($this->returnValue($invoice));

        $this
            ->invoiceSender
            ->expects($this->once())
            ->method('send')
            ->will($this->returnValue($invoice));

        $orderB
            ->expects($this->once())
            ->method('addStatusHistoryComment')
            ->will($this->returnValue($orderB));

        $orderB
            ->expects($this->once())
            ->method('setIsCustomerNotified')
            ->with(true)
            ->will($this->returnValue($orderB));

        $orderB
            ->expects($this->once())
            ->method('save')
            ->will($this->returnValue($orderB));

        $this
            ->fileA
            ->expects($this->once())
            ->method('createNewEvent');

        $this
            ->fileB
            ->expects($this->once())
            ->method('createNewEvent');

        $this
            ->fileC
            ->expects($this->once())
            ->method('createNewEvent');

        $this
            ->fileD
            ->expects($this->once())
            ->method('createNewEvent');

        $this->subject->processFiles();
    }

}