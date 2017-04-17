<?php

namespace Gabrielqs\Boleto\Test\Unit\Model\Returns\File;

use \Magento\Framework\Exception\CouldNotDeleteException;
use \Magento\Framework\Exception\CouldNotSaveException;
use \Magento\Framework\Exception\NoSuchEntityException;
use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Magento\Framework\Api\SortOrder;
use \Gabrielqs\Boleto\Model\Returns\File\OrderRepository as Subject;
use \Gabrielqs\Boleto\Model\ResourceModel\Returns\File\Order as ReturnsFileOrderResource;
use \Gabrielqs\Boleto\Model\Returns\File\OrderFactory as ReturnsFileOrderFactory;
use \Gabrielqs\Boleto\Model\ResourceModel\Returns\File\Order\CollectionFactory as ReturnsFileOrderCollectionFactory;
use \Gabrielqs\Boleto\Model\ResourceModel\Returns\File\Order\Collection as ReturnsFileOrderCollection;
use \Gabrielqs\Boleto\Model\Returns\File\Order as ReturnsFileOrder;
use \Gabrielqs\Boleto\Api\Data\ReturnsFileOrderSearchResultsInterfaceFactory;



/**
 * Unit Testcase
 */
class OrderRepositoryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var String
     */
    protected $className = null;

    /**
     * @var ObjectManager
     */
    protected $objectManager = null;

    /**rub
     * @var Subject
     */
    protected $originalSubject = null;

    /**
     * Returns File Resource
     * @var ReturnsFileOrderResource
     */
    protected $resource;

    /**
     * File Factory
     * @var ReturnsFileOrderFactory
     */
    protected $returnsFileOrderFactory;

    /**
     * File Collection Factory
     * @var ReturnsFileOrderSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

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
    }

    protected function getConstructorArguments()
    {
        $arguments = $this->objectManager->getConstructArguments($this->className);

        $this->resource = $this->getMockBuilder(ReturnsFileOrderResource::class)
            ->setMethods(['save', 'load', 'delete'])
            ->disableOriginalConstructor()
            ->getMock();
        $arguments['resource'] = $this->resource;

        $this->returnsFileOrderFactory = $this->getMockBuilder(ReturnsFileOrderFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $arguments['returnsFileOrderFactory'] = $this->returnsFileOrderFactory;

        $this->searchResultsFactory = $this->getMockBuilder(ReturnsFileOrderSearchResultsInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $arguments['searchResultsFactory'] = $this->searchResultsFactory;

        return $arguments;
    }

    public function testSaveReturnsCouldNotSaveExceptionOnException()
    {
        $returnsFileOrder = $this->getMockBuilder(ReturnsFileOrder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this
            ->resource
            ->expects($this->once())
            ->method('save')
            ->will($this->throwException(new \Exception()));

        $this->setExpectedException(CouldNotSaveException::class);
        $this->subject->save($returnsFileOrder);
    }

    public function testSaveReturnsSuccessfullySavesEntityToResourceAndReturnsIt()
    {
        $returnsFileOrder = $this->getMockBuilder(ReturnsFileOrder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this
            ->resource
            ->expects($this->once())
            ->method('save')
            ->will($this->returnValue($returnsFileOrder));

        $return = $this->subject->save($returnsFileOrder);

        $this->assertEquals($return, $returnsFileOrder);
    }

    public function testGetByIdThrowsNoSuchEntityExceptionWhenEntityNotFound()
    {
        $returnsFileOrder = $this->getMockBuilder(ReturnsFileOrder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $returnsFileOrder
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(null));

        $this
            ->returnsFileOrderFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($returnsFileOrder));

        $this
            ->resource
            ->expects($this->once())
            ->method('load')
            ->with($returnsFileOrder, 324)
            ->will($this->returnValue($returnsFileOrder));

        $this->setExpectedException(NoSuchEntityException::class);
        $this->subject->getById(324);

    }

    public function testGetByIdReturnsLoadedEntityWhenEntityFound()
    {
        $returnsFileOrder = $this->getMockBuilder(ReturnsFileOrder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $returnsFileOrder
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(324));

        $this
            ->returnsFileOrderFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($returnsFileOrder));

        $this
            ->resource
            ->expects($this->once())
            ->method('load')
            ->with($returnsFileOrder, 324)
            ->will($this->returnValue($returnsFileOrder));

        $return = $this->subject->getById(324);

        $this->assertEquals($returnsFileOrder, $return);

    }
    
    public function testDeleteByIdUsesResourceToDeleteAndReturnsTrue()
    {
        $returnsFileOrderId = '123';

        $returnsFileOrder = $this->getMockBuilder(ReturnsFileOrder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $returnsFileOrder->expects($this->any())
            ->method('getId')
            ->willReturn($returnsFileOrderId);

        $this
            ->returnsFileOrderFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($returnsFileOrder));

        $this
            ->resource
            ->expects($this->once())
            ->method('load')
            ->with($returnsFileOrder, $returnsFileOrderId)
            ->willReturn($returnsFileOrder);

        $this
            ->resource
            ->expects($this->once())
            ->method('delete')
            ->with($returnsFileOrder)
            ->willReturnSelf();

        $this->assertTrue($this->subject->deleteById($returnsFileOrderId));
    }

    public function testDeleteByIdThrowsExceptionWhenEntityNotFound()
    {
        $returnsFileOrderId = '123';

        $returnsFileOrder = $this->getMockBuilder(ReturnsFileOrder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $returnsFileOrder->expects($this->any())
            ->method('getId')
            ->willReturn(false);

        $this
            ->returnsFileOrderFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($returnsFileOrder));

        $this
            ->resource
            ->expects($this->once())
            ->method('load')
            ->with($returnsFileOrder, $returnsFileOrderId)
            ->will($this->throwException(new NoSuchEntityException()));

        $this
            ->resource
            ->expects($this->never())
            ->method('delete')
            ->willReturnSelf();

        $this->setExpectedException(NoSuchEntityException::class);

        $this->assertTrue($this->subject->deleteById($returnsFileOrderId));
    }

    public function testDeleteByIdThrowsExceptionWhenCouldNotDelete()
    {
        $returnsFileOrderId = '123';

        $returnsFileOrder = $this->getMockBuilder(ReturnsFileOrder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $returnsFileOrder
            ->expects($this->any())
            ->method('getId')
            ->willReturn($returnsFileOrderId);

        $this
            ->returnsFileOrderFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($returnsFileOrder);

        $this
            ->resource
            ->expects($this->once())
            ->method('load')
            ->with($returnsFileOrder, $returnsFileOrderId)
            ->willReturn($returnsFileOrder);

        $this
            ->resource
            ->expects($this->once())
            ->method('delete')
            ->will($this->throwException(new \Exception()));

        $this->setExpectedException(CouldNotDeleteException::class);

        $this->assertTrue($this->subject->deleteById($returnsFileOrderId));
    }

    public function testGetList()
    {
        $field = 'name';
        $value = 'magento';
        $condition = 'eq';
        $currentPage = 3;
        $pageSize = 2;
        $sortField = 'id';

        $criteria = $this->getMockBuilder('Magento\Framework\Api\SearchCriteriaInterface')->getMock();
        $filterGroup = $this->getMockBuilder('Magento\Framework\Api\Search\FilterGroup')->getMock();
        $filter = $this->getMockBuilder('Magento\Framework\Api\Filter')->getMock();
        $storeFilter = $this->getMockBuilder('Magento\Framework\Api\Filter')->getMock();
        $sortOrder = $this->getMockBuilder('Magento\Framework\Api\SortOrder')->getMock();

        $criteria->expects($this->once())->method('getFilterGroups')->willReturn([$filterGroup]);
        $criteria->expects($this->once())->method('getSortOrders')->willReturn([$sortOrder]);
        $criteria->expects($this->once())->method('getCurrentPage')->willReturn($currentPage);
        $criteria->expects($this->once())->method('getPageSize')->willReturn($pageSize);
        $filterGroup->expects($this->once())->method('getFilters')->willReturn([$storeFilter, $filter]);
        $filter->expects($this->any())->method('getConditionType')->willReturn($condition);
        $filter->expects($this->any())->method('getField')->willReturn($field);
        $filter->expects($this->once())->method('getValue')->willReturn($value);
        $storeFilter->expects($this->any())->method('getField')->willReturn('store_id');
        $storeFilter->expects($this->once())->method('getValue')->willReturn(1);
        $sortOrder->expects($this->once())->method('getField')->willReturn($sortField);
        $sortOrder->expects($this->once())->method('getDirection')->willReturn(SortOrder::SORT_DESC);

        $returnsFileOrderCollection = $this->getMockBuilder(ReturnsFileOrderCollection::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'addOrder',
                'setSearchCriteria',
                'setCurPage',
                'setPageSize',
                'addFieldToFilter'
                ])
            ->getMock();

        $this
            ->searchResultsFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($returnsFileOrderCollection));

        $returnsFileOrderCollection
            ->expects($this->once())
            ->method('addFieldToFilter')
            ->with([ 'store_id', 'name' ],
            [
                [ 'eq' => 1 ],
                [ 'eq' => 'magento' ]
            ])
            ->willReturnSelf();

        $returnsFileOrderCollection->expects($this->once())->method('setCurPage')->with($currentPage)->willReturnSelf();
        $returnsFileOrderCollection->expects($this->once())->method('setPageSize')->with($pageSize)->willReturnSelf();
        $returnsFileOrderCollection->expects($this->once())->method('addOrder')->with($sortField, 'DESC')->willReturnSelf();

        $this->assertEquals($returnsFileOrderCollection, $this->subject->getList($criteria));
    }

    public function testGetListNoSortOrdersCreatesArray()
    {
        $field = 'name';
        $value = 'magento';
        $condition = 'eq';
        $currentPage = 3;
        $pageSize = 2;
        $sortField = 'id';

        $criteria = $this->getMockBuilder('Magento\Framework\Api\SearchCriteriaInterface')->getMock();
        $filterGroup = $this->getMockBuilder('Magento\Framework\Api\Search\FilterGroup')->getMock();
        $filter = $this->getMockBuilder('Magento\Framework\Api\Filter')->getMock();
        $storeFilter = $this->getMockBuilder('Magento\Framework\Api\Filter')->getMock();
        $sortOrder = $this->getMockBuilder('Magento\Framework\Api\SortOrder')->getMock();

        $criteria->expects($this->once())->method('getFilterGroups')->willReturn([$filterGroup]);
        $criteria->expects($this->once())->method('getSortOrders')->willReturn(null);
        $criteria->expects($this->once())->method('getCurrentPage')->willReturn($currentPage);
        $criteria->expects($this->once())->method('getPageSize')->willReturn($pageSize);
        $filterGroup->expects($this->once())->method('getFilters')->willReturn([$storeFilter, $filter]);
        $filter->expects($this->any())->method('getConditionType')->willReturn($condition);
        $filter->expects($this->any())->method('getField')->willReturn($field);
        $filter->expects($this->once())->method('getValue')->willReturn($value);
        $storeFilter->expects($this->any())->method('getField')->willReturn('store_id');
        $storeFilter->expects($this->once())->method('getValue')->willReturn(1);

        $returnsFileOrderCollection = $this->getMockBuilder(ReturnsFileOrderCollection::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setSearchCriteria',
                'setCurPage',
                'setPageSize',
                'addFieldToFilter'
                ])
            ->getMock();

        $this
            ->searchResultsFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($returnsFileOrderCollection));

        $returnsFileOrderCollection
            ->expects($this->once())
            ->method('addFieldToFilter')
            ->with([ 'store_id', 'name' ],
            [
                [ 'eq' => 1 ],
                [ 'eq' => 'magento' ]
            ])
            ->willReturnSelf();

        $returnsFileOrderCollection->expects($this->once())->method('setCurPage')->with($currentPage)->willReturnSelf();
        $returnsFileOrderCollection->expects($this->once())->method('setPageSize')->with($pageSize)->willReturnSelf();

        $this->assertEquals($returnsFileOrderCollection, $this->subject->getList($criteria));
    }

}