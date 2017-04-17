<?php

namespace Gabrielqs\Boleto\Test\Unit\Model\Returns\File;

use \Magento\Framework\Exception\CouldNotDeleteException;
use \Magento\Framework\Exception\CouldNotSaveException;
use \Magento\Framework\Exception\NoSuchEntityException;
use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Magento\Framework\Api\SortOrder;
use \Gabrielqs\Boleto\Model\Returns\File\EventRepository as Subject;
use \Gabrielqs\Boleto\Model\ResourceModel\Returns\File\Event as ReturnsFileEventResource;
use \Gabrielqs\Boleto\Model\Returns\File\EventFactory as ReturnsFileEventFactory;
use \Gabrielqs\Boleto\Model\ResourceModel\Returns\File\Event\CollectionFactory as ReturnsFileEventCollectionFactory;
use \Gabrielqs\Boleto\Model\ResourceModel\Returns\File\Event\Collection as ReturnsFileEventCollection;
use \Gabrielqs\Boleto\Model\Returns\File\Event as ReturnsFileEvent;
use \Gabrielqs\Boleto\Api\Data\ReturnsFileEventSearchResultsInterfaceFactory;



/**
 * Unit Testcase
 */
class EventRepositoryTest extends \PHPUnit_Framework_TestCase
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
     * Returns File Resource
     * @var ReturnsFileEventResource
     */
    protected $resource;

    /**
     * File Factory
     * @var ReturnsFileEventFactory
     */
    protected $returnsFileEventFactory;

    /**
     * File Collection Factory
     * @var ReturnsFileEventSearchResultsInterfaceFactory
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

        $this->resource = $this->getMockBuilder(ReturnsFileEventResource::class)
            ->setMethods(['save', 'load', 'delete'])
            ->disableOriginalConstructor()
            ->getMock();
        $arguments['resource'] = $this->resource;

        $this->returnsFileEventFactory = $this->getMockBuilder(ReturnsFileEventFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $arguments['returnsFileEventFactory'] = $this->returnsFileEventFactory;

        $this->searchResultsFactory = $this->getMockBuilder(ReturnsFileEventSearchResultsInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $arguments['searchResultsFactory'] = $this->searchResultsFactory;

        return $arguments;
    }

    public function testSaveReturnsCouldNotSaveExceptionOnException()
    {
        $returnsFileEvent = $this->getMockBuilder(ReturnsFileEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this
            ->resource
            ->expects($this->once())
            ->method('save')
            ->will($this->throwException(new \Exception()));

        $this->setExpectedException(CouldNotSaveException::class);
        $this->subject->save($returnsFileEvent);
    }

    public function testSaveReturnsSuccessfullySavesEntityToResourceAndReturnsIt()
    {
        $returnsFileEvent = $this->getMockBuilder(ReturnsFileEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this
            ->resource
            ->expects($this->once())
            ->method('save')
            ->will($this->returnValue($returnsFileEvent));

        $return = $this->subject->save($returnsFileEvent);

        $this->assertEquals($return, $returnsFileEvent);
    }

    public function testGetByIdThrowsNoSuchEntityExceptionWhenEntityNotFound()
    {
        $returnsFileEvent = $this->getMockBuilder(ReturnsFileEvent::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $returnsFileEvent
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(null));

        $this
            ->returnsFileEventFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($returnsFileEvent));

        $this
            ->resource
            ->expects($this->once())
            ->method('load')
            ->with($returnsFileEvent, 324)
            ->will($this->returnValue($returnsFileEvent));

        $this->setExpectedException(NoSuchEntityException::class);
        $this->subject->getById(324);

    }

    public function testGetByIdReturnsLoadedEntityWhenEntityFound()
    {
        $returnsFileEvent = $this->getMockBuilder(ReturnsFileEvent::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $returnsFileEvent
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(324));

        $this
            ->returnsFileEventFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($returnsFileEvent));

        $this
            ->resource
            ->expects($this->once())
            ->method('load')
            ->with($returnsFileEvent, 324)
            ->will($this->returnValue($returnsFileEvent));

        $return = $this->subject->getById(324);

        $this->assertEquals($returnsFileEvent, $return);

    }
    
    public function testDeleteByIdUsesResourceToDeleteAndReturnsTrue()
    {
        $returnsFileEventId = '123';

        $returnsFileEvent = $this->getMockBuilder(ReturnsFileEvent::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $returnsFileEvent->expects($this->any())
            ->method('getId')
            ->willReturn($returnsFileEventId);

        $this
            ->returnsFileEventFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($returnsFileEvent));

        $this
            ->resource
            ->expects($this->once())
            ->method('load')
            ->with($returnsFileEvent, $returnsFileEventId)
            ->willReturn($returnsFileEvent);

        $this
            ->resource
            ->expects($this->once())
            ->method('delete')
            ->with($returnsFileEvent)
            ->willReturnSelf();

        $this->assertTrue($this->subject->deleteById($returnsFileEventId));
    }

    public function testDeleteByIdThrowsExceptionWhenEntityNotFound()
    {
        $returnsFileEventId = '123';

        $returnsFileEvent = $this->getMockBuilder(ReturnsFileEvent::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $returnsFileEvent->expects($this->any())
            ->method('getId')
            ->willReturn(false);

        $this
            ->returnsFileEventFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($returnsFileEvent));

        $this
            ->resource
            ->expects($this->once())
            ->method('load')
            ->with($returnsFileEvent, $returnsFileEventId)
            ->will($this->throwException(new NoSuchEntityException()));

        $this
            ->resource
            ->expects($this->never())
            ->method('delete')
            ->willReturnSelf();

        $this->setExpectedException(NoSuchEntityException::class);

        $this->assertTrue($this->subject->deleteById($returnsFileEventId));
    }

    public function testDeleteByIdThrowsExceptionWhenCouldNotDelete()
    {
        $returnsFileEventId = '123';

        $returnsFileEvent = $this->getMockBuilder(ReturnsFileEvent::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $returnsFileEvent
            ->expects($this->any())
            ->method('getId')
            ->willReturn($returnsFileEventId);

        $this
            ->returnsFileEventFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($returnsFileEvent);

        $this
            ->resource
            ->expects($this->once())
            ->method('load')
            ->with($returnsFileEvent, $returnsFileEventId)
            ->willReturn($returnsFileEvent);

        $this
            ->resource
            ->expects($this->once())
            ->method('delete')
            ->will($this->throwException(new \Exception()));

        $this->setExpectedException(CouldNotDeleteException::class);

        $this->assertTrue($this->subject->deleteById($returnsFileEventId));
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

        $returnsFileEventCollection = $this->getMockBuilder(ReturnsFileEventCollection::class)
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
            ->will($this->returnValue($returnsFileEventCollection));

        $returnsFileEventCollection
            ->expects($this->once())
            ->method('addFieldToFilter')
            ->with([ 'store_id', 'name' ],
            [
                [ 'eq' => 1 ],
                [ 'eq' => 'magento' ]
            ])
            ->willReturnSelf();

        $returnsFileEventCollection->expects($this->once())->method('setCurPage')->with($currentPage)->willReturnSelf();
        $returnsFileEventCollection->expects($this->once())->method('setPageSize')->with($pageSize)->willReturnSelf();
        $returnsFileEventCollection->expects($this->once())->method('addOrder')->with($sortField, 'DESC')->willReturnSelf();

        $this->assertEquals($returnsFileEventCollection, $this->subject->getList($criteria));
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

        $returnsFileEventCollection = $this->getMockBuilder(ReturnsFileEventCollection::class)
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
            ->will($this->returnValue($returnsFileEventCollection));

        $returnsFileEventCollection
            ->expects($this->once())
            ->method('addFieldToFilter')
            ->with([ 'store_id', 'name' ],
            [
                [ 'eq' => 1 ],
                [ 'eq' => 'magento' ]
            ])
            ->willReturnSelf();

        $returnsFileEventCollection->expects($this->once())->method('setCurPage')->with($currentPage)->willReturnSelf();
        $returnsFileEventCollection->expects($this->once())->method('setPageSize')->with($pageSize)->willReturnSelf();

        $this->assertEquals($returnsFileEventCollection, $this->subject->getList($criteria));
    }

}