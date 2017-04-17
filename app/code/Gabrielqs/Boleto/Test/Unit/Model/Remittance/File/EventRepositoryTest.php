<?php

namespace Gabrielqs\Boleto\Test\Unit\Model\Remittance\File;

use \Magento\Framework\Exception\CouldNotDeleteException;
use \Magento\Framework\Exception\CouldNotSaveException;
use \Magento\Framework\Exception\NoSuchEntityException;
use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Magento\Framework\Api\SortOrder;
use \Gabrielqs\Boleto\Model\Remittance\File\EventRepository as Subject;
use \Gabrielqs\Boleto\Model\ResourceModel\Remittance\File\Event as RemittanceFileEventResource;
use \Gabrielqs\Boleto\Model\Remittance\File\EventFactory as RemittanceFileEventFactory;
use \Gabrielqs\Boleto\Model\ResourceModel\Remittance\File\Event\CollectionFactory as RemittanceFileEventCollectionFactory;
use \Gabrielqs\Boleto\Model\ResourceModel\Remittance\File\Event\Collection as RemittanceFileEventCollection;
use \Gabrielqs\Boleto\Model\Remittance\File\Event as RemittanceFileEvent;
use \Gabrielqs\Boleto\Api\Data\RemittanceFileEventSearchResultsInterfaceFactory;



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
     * Remittance File Resource
     * @var RemittanceFileEventResource
     */
    protected $resource;

    /**
     * File Factory
     * @var RemittanceFileEventFactory
     */
    protected $remittanceFileEventFactory;

    /**
     * File Collection Factory
     * @var RemittanceFileEventSearchResultsInterfaceFactory
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

        $this->resource = $this->getMockBuilder(RemittanceFileEventResource::class)
            ->setMethods(['save', 'load', 'delete'])
            ->disableOriginalConstructor()
            ->getMock();
        $arguments['resource'] = $this->resource;

        $this->remittanceFileEventFactory = $this->getMockBuilder(RemittanceFileEventFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $arguments['remittanceFileEventFactory'] = $this->remittanceFileEventFactory;

        $this->searchResultsFactory = $this->getMockBuilder(RemittanceFileEventSearchResultsInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $arguments['searchResultsFactory'] = $this->searchResultsFactory;

        return $arguments;
    }

    public function testSaveRemittanceCouldNotSaveExceptionOnException()
    {
        $remittanceFileEvent = $this->getMockBuilder(RemittanceFileEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this
            ->resource
            ->expects($this->once())
            ->method('save')
            ->will($this->throwException(new \Exception()));

        $this->setExpectedException(CouldNotSaveException::class);
        $this->subject->save($remittanceFileEvent);
    }

    public function testSaveRemittanceSuccessfullySavesEntityToResourceAndRemittanceIt()
    {
        $remittanceFileEvent = $this->getMockBuilder(RemittanceFileEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this
            ->resource
            ->expects($this->once())
            ->method('save')
            ->will($this->returnValue($remittanceFileEvent));

        $return = $this->subject->save($remittanceFileEvent);

        $this->assertEquals($return, $remittanceFileEvent);
    }

    public function testGetByIdThrowsNoSuchEntityExceptionWhenEntityNotFound()
    {
        $remittanceFileEvent = $this->getMockBuilder(RemittanceFileEvent::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $remittanceFileEvent
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(null));

        $this
            ->remittanceFileEventFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($remittanceFileEvent));

        $this
            ->resource
            ->expects($this->once())
            ->method('load')
            ->with($remittanceFileEvent, 324)
            ->will($this->returnValue($remittanceFileEvent));

        $this->setExpectedException(NoSuchEntityException::class);
        $this->subject->getById(324);

    }

    public function testGetByIdRemittanceLoadedEntityWhenEntityFound()
    {
        $remittanceFileEvent = $this->getMockBuilder(RemittanceFileEvent::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $remittanceFileEvent
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(324));

        $this
            ->remittanceFileEventFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($remittanceFileEvent));

        $this
            ->resource
            ->expects($this->once())
            ->method('load')
            ->with($remittanceFileEvent, 324)
            ->will($this->returnValue($remittanceFileEvent));

        $return = $this->subject->getById(324);

        $this->assertEquals($remittanceFileEvent, $return);

    }
    
    public function testDeleteByIdUsesResourceToDeleteAndRemittanceTrue()
    {
        $remittanceFileEventId = '123';

        $remittanceFileEvent = $this->getMockBuilder(RemittanceFileEvent::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $remittanceFileEvent->expects($this->any())
            ->method('getId')
            ->willReturn($remittanceFileEventId);

        $this
            ->remittanceFileEventFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($remittanceFileEvent));

        $this
            ->resource
            ->expects($this->once())
            ->method('load')
            ->with($remittanceFileEvent, $remittanceFileEventId)
            ->willReturn($remittanceFileEvent);

        $this
            ->resource
            ->expects($this->once())
            ->method('delete')
            ->with($remittanceFileEvent)
            ->willReturnSelf();

        $this->assertTrue($this->subject->deleteById($remittanceFileEventId));
    }

    public function testDeleteByIdThrowsExceptionWhenEntityNotFound()
    {
        $remittanceFileEventId = '123';

        $remittanceFileEvent = $this->getMockBuilder(RemittanceFileEvent::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $remittanceFileEvent->expects($this->any())
            ->method('getId')
            ->willReturn(false);

        $this
            ->remittanceFileEventFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($remittanceFileEvent));

        $this
            ->resource
            ->expects($this->once())
            ->method('load')
            ->with($remittanceFileEvent, $remittanceFileEventId)
            ->will($this->throwException(new NoSuchEntityException()));

        $this
            ->resource
            ->expects($this->never())
            ->method('delete')
            ->willReturnSelf();

        $this->setExpectedException(NoSuchEntityException::class);

        $this->assertTrue($this->subject->deleteById($remittanceFileEventId));
    }

    public function testDeleteByIdThrowsExceptionWhenCouldNotDelete()
    {
        $remittanceFileEventId = '123';

        $remittanceFileEvent = $this->getMockBuilder(RemittanceFileEvent::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $remittanceFileEvent
            ->expects($this->any())
            ->method('getId')
            ->willReturn($remittanceFileEventId);

        $this
            ->remittanceFileEventFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($remittanceFileEvent);

        $this
            ->resource
            ->expects($this->once())
            ->method('load')
            ->with($remittanceFileEvent, $remittanceFileEventId)
            ->willReturn($remittanceFileEvent);

        $this
            ->resource
            ->expects($this->once())
            ->method('delete')
            ->will($this->throwException(new \Exception()));

        $this->setExpectedException(CouldNotDeleteException::class);

        $this->assertTrue($this->subject->deleteById($remittanceFileEventId));
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

        $remittanceFileEventCollection = $this->getMockBuilder(RemittanceFileEventCollection::class)
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
            ->will($this->returnValue($remittanceFileEventCollection));

        $remittanceFileEventCollection
            ->expects($this->once())
            ->method('addFieldToFilter')
            ->with([ 'store_id', 'name' ],
            [
                [ 'eq' => 1 ],
                [ 'eq' => 'magento' ]
            ])
            ->willReturnSelf();

        $remittanceFileEventCollection->expects($this->once())->method('setCurPage')->with($currentPage)->willReturnSelf();
        $remittanceFileEventCollection->expects($this->once())->method('setPageSize')->with($pageSize)->willReturnSelf();
        $remittanceFileEventCollection->expects($this->once())->method('addOrder')->with($sortField, 'DESC')->willReturnSelf();

        $this->assertEquals($remittanceFileEventCollection, $this->subject->getList($criteria));
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

        $remittanceFileEventCollection = $this->getMockBuilder(RemittanceFileEventCollection::class)
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
            ->will($this->returnValue($remittanceFileEventCollection));

        $remittanceFileEventCollection
            ->expects($this->once())
            ->method('addFieldToFilter')
            ->with([ 'store_id', 'name' ],
            [
                [ 'eq' => 1 ],
                [ 'eq' => 'magento' ]
            ])
            ->willReturnSelf();

        $remittanceFileEventCollection->expects($this->once())->method('setCurPage')->with($currentPage)->willReturnSelf();
        $remittanceFileEventCollection->expects($this->once())->method('setPageSize')->with($pageSize)->willReturnSelf();

        $this->assertEquals($remittanceFileEventCollection, $this->subject->getList($criteria));
    }

}