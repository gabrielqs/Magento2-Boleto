<?php

namespace Gabrielqs\Boleto\Test\Unit\Helper\Returns\Reader;

use \Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Magento\Framework\Api\SearchCriteria;
use \Magento\Sales\Model\Order;
use \Magento\Sales\Model\OrderRepository;
use \Gabrielqs\Boleto\Model\Returns\FileRepository as ReturnsFileRepository;
use \Gabrielqs\Boleto\Helper\Returns\Reader as Subject;
use \Gabrielqs\Boleto\Model\Returns\FileFactory as ReturnsFileFactory;
use \Gabrielqs\Boleto\Model\Returns\File as ReturnsFile;
use \Gabrielqs\Boleto\Helper\Boleto\Data as BoletoHelper;

/**
 * Unit Testcase
 */
class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Boleto Helper
     * @var BoletoHelper
     */
    protected $boletoHelper = null;

    /**
     * @var String
     */
    protected $className = null;

    /**
     * @var ObjectManager
     */
    protected $objectManager = null;

    /**
     * Order Repository
     * @var OrderRepository $orderRepository
     */
    protected $orderRepository = null;

    /**
     * @var Subject
     */
    protected $originalSubject = null;

    /**
     * Returns File Factory
     * @var ReturnsFileFactory
     */
    protected $returnsFileFactory;

    /**
     * Returns File Repository
     * @var ReturnsFileRepository $returnsFileRepository
     */
    protected $returnsFileRepository = null;

    /**
     * @var Subject
     */
    protected $subject = null;

    /**
     * @var SearchCriteria
     */
    protected $searchCriteria = null;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder = null;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->className = Subject::class;
        $arguments = $this->getConstructorArguments();

        $this->subject = $this
            ->getMockBuilder($this->className)
            ->setConstructorArgs($arguments)
            ->setMethods(['_getDetailsFromPath', '_getReturnsFileOrderIdsFromPath'])
            ->getMock();

        $this->originalSubject = $this
            ->objectManager
            ->getObject($this->className, $arguments);
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

        $this->returnsFileRepository = $this->getMockBuilder(ReturnsFileRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getList', 'save'])
            ->getMock();
        $arguments['returnsFileRepository'] = $this->returnsFileRepository;

        $this->orderRepository = $this->getMockBuilder(OrderRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getList'])
            ->getMock();
        $arguments['orderRepository'] = $this->orderRepository;

        $this->returnsFileFactory = $this->getMockBuilder(ReturnsFileFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $arguments['returnsFileFactory'] = $this->returnsFileFactory;

        $this->boletoHelper = $this->getMockBuilder(BoletoHelper::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $arguments['boletoHelper'] = $this->boletoHelper;

        return $arguments;
    }

    public function testReturnsFileExistsReturnsTrueWhenFileExists()
    {
        $fileName = 'CN33020.ret';
        $filePath = '/foo/bar/' . $fileName;

        $this
            ->searchCriteriaBuilder
            ->expects($this->once())
            ->method('addFilter')
            ->with(ReturnsFile::NAME, $fileName, 'eq')
            ->will($this->returnValue($this->searchCriteriaBuilder));

        $this
            ->searchCriteriaBuilder
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->searchCriteria));

        $collection = $this->getMockBuilder(DataObject::class)
            ->setMethods(['getTotalCount'])
            ->disableOriginalConstructor()
            ->getMock();

        $collection
            ->expects($this->once())
            ->method('getTotalCount')
            ->will($this->returnValue(1));

        $this
            ->returnsFileRepository
            ->expects($this->once())
            ->method('getList')
            ->with($this->searchCriteria)
            ->will($this->returnValue($collection));

        $return = $this->subject->returnsFileExists($filePath);

        $this->assertEquals(true, $return);
    }

    public function testReturnsFileExistsReturnsFalseWhenFileDoesntExist()
    {
        $fileName = 'CN33020.ret';
        $filePath = '/foo/bar/' . $fileName;

        $this
            ->searchCriteriaBuilder
            ->expects($this->once())
            ->method('addFilter')
            ->with(ReturnsFile::NAME, $fileName)
            ->will($this->returnValue($this->searchCriteriaBuilder));

        $this
            ->searchCriteriaBuilder
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->searchCriteria));

        $collection = $this->getMockBuilder(DataObject::class)
            ->setMethods(['getTotalCount'])
            ->disableOriginalConstructor()
            ->getMock();

        $collection
            ->expects($this->once())
            ->method('getTotalCount')
            ->will($this->returnValue(0));

        $this
            ->returnsFileRepository
            ->expects($this->once())
            ->method('getList')
            ->with($this->searchCriteria)
            ->will($this->returnValue($collection));

        $return = $this->subject->returnsFileExists($filePath);

        $this->assertEquals(false, $return);
    }

    public function testGetDestinationPathGetsItFromReturnsFileModel()
    {
        $returnsFile = $this->getMockBuilder(ReturnsFile::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStoragePath'])
            ->getMock();

        $this
            ->returnsFileFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($returnsFile));

        $returnsFile
            ->expects($this->once())
            ->method('getStoragePath')
            ->will($this->returnValue('/storege/path'));

        $return = $this->subject->getDestinationPath();
        $this->assertEquals('/storege/path', $return);
    }

    public function testGetOrdersIdsAndValuesReturnFormat()
    {
        $returnsFile = $this->getMockBuilder(ReturnsFile::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPath'])
            ->getMock();

        $returnsFile
            ->expects($this->once())
            ->method('getPath')
            ->will($this->returnValue('/var/www/path/to/return/file/CEM0e293b.ret'));

        $detailA = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNossoNumero', 'getValorRecebido'])
            ->getMock();

        $detailA
            ->expects($this->once())
            ->method('getNossoNumero')
            ->will($this->returnValue('00003012'));

        $detailA
            ->expects($this->once())
            ->method('getValorRecebido')
            ->will($this->returnValue(1039.93));

        $detailB = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNossoNumero', 'getValorRecebido'])
            ->getMock();

        $detailB
            ->expects($this->once())
            ->method('getNossoNumero')
            ->will($this->returnValue(38));

        $detailB
            ->expects($this->once())
            ->method('getValorRecebido')
            ->will($this->returnValue(23.38));

        $detailsList = [$detailA, $detailB];

        $this
            ->subject
            ->expects($this->once())
            ->method('_getDetailsFromPath')
            ->with('/var/www/path/to/return/file/CEM0e293b.ret')
            ->will($this->returnValue($detailsList));

        $expectedA = new \stdClass();
        $expectedA->orderId = '000003012';
        $expectedA->value = 1039.93;

        $expectedB = new \stdClass();
        $expectedB->orderId = '000000038';
        $expectedB->value = 23.38;
        $expectedReturn = [$expectedA, $expectedB];

        $return = $this->subject->getOrdersIdsAndValues($returnsFile);

        $this->assertEquals($expectedReturn, $return);
    }


    public function testReadAndSaveReturnsFileCreatesAllRelatedEntitites()
    {
        $fileName = 'CN33020.ret';
        $filePath = '/foo/bar/' . $fileName;

        $returnsFile = $this->getMockBuilder(ReturnsFile::class)
            ->disableOriginalConstructor()
            ->setMethods(['setName', 'setStatus', 'createNewOrderByIncrementId', 'createNewEvent'])
            ->getMock();

        $returnsFile
            ->expects($this->once())
            ->method('setName')
            ->with($fileName)
            ->will($this->returnValue($returnsFile));

        $returnsFile
            ->expects($this->once())
            ->method('setStatus')
            ->with(ReturnsFile::STATUS_NEW)
            ->will($this->returnValue($returnsFile));

        $this
            ->returnsFileFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($returnsFile));

        $this
            ->returnsFileRepository
            ->expects($this->once())
            ->method('save')
            ->with($returnsFile)
            ->will($this->returnValue($returnsFile));

        $this
            ->subject
            ->expects($this->once())
            ->method('_getReturnsFileOrderIdsFromPath')
            ->with($filePath)
            ->will($this->returnValue(['000003214', '000003451']));

        $returnsFile
            ->expects($this->exactly(2))
            ->method('createNewOrderByIncrementId')
            ->withConsecutive(
                ['000003214'],
                ['000003451']
            );

        $returnsFile
            ->expects($this->once())
            ->method('createNewEvent')
            ->with('File Imported');

        $this->subject->readAndSaveReturnsFile($filePath);
    }

    public function testValidateReturnsFileThrowsExceptionWhenNoRowCorrespondsToExistingOrder()
    {
        $fileName = 'CN33020.ret';
        $filePath = '/foo/bar/' . $fileName;

        $orderIds = ['000003214', '000003451'];
        $this
            ->subject
            ->expects($this->once())
            ->method('_getReturnsFileOrderIdsFromPath')
            ->with($filePath)
            ->will($this->returnValue($orderIds));

        $this
            ->searchCriteriaBuilder
            ->expects($this->once())
            ->method('addFilter')
            ->with(Order::INCREMENT_ID, $orderIds, 'in')
            ->will($this->returnValue($this->searchCriteriaBuilder));
        $this
            ->searchCriteriaBuilder
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->searchCriteria));

        $collection = $this->getMockBuilder(DataObject::class)
            ->setMethods(['getTotalCount'])
            ->disableOriginalConstructor()
            ->getMock();

        $collection
            ->expects($this->once())
            ->method('getTotalCount')
            ->will($this->returnValue(0));

        $this
            ->orderRepository
            ->expects($this->once())
            ->method('getList')
            ->with($this->searchCriteria)
            ->will($this->returnValue($collection));

        $this->setExpectedException(LocalizedException::class);

        $this->subject->validateReturnsFile($filePath);
    }

    public function testValidateReturnsFileReturnsTrueWhenAtLeastOneOrderIsFound()
    {
        $fileName = 'CN33020.ret';
        $filePath = '/foo/bar/' . $fileName;

        $orderIds = ['000003214', '000003451'];
        $this
            ->subject
            ->expects($this->once())
            ->method('_getReturnsFileOrderIdsFromPath')
            ->with($filePath)
            ->will($this->returnValue($orderIds));

        $this
            ->searchCriteriaBuilder
            ->expects($this->once())
            ->method('addFilter')
            ->with(Order::INCREMENT_ID, $orderIds, 'in')
            ->will($this->returnValue($this->searchCriteriaBuilder));
        $this
            ->searchCriteriaBuilder
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->searchCriteria));

        $collection = $this->getMockBuilder(DataObject::class)
            ->setMethods(['getTotalCount'])
            ->disableOriginalConstructor()
            ->getMock();

        $collection
            ->expects($this->once())
            ->method('getTotalCount')
            ->will($this->returnValue(2));

        $this
            ->orderRepository
            ->expects($this->once())
            ->method('getList')
            ->with($this->searchCriteria)
            ->will($this->returnValue($collection));

        $return = $this->subject->validateReturnsFile($filePath);
        $this->assertTrue($return);
    }

}