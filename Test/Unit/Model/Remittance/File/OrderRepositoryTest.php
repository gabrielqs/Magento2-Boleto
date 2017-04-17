<?php

namespace Gabrielqs\Boleto\Test\Unit\Model\Remittance\File;

use \Magento\Framework\Exception\CouldNotDeleteException;
use \Magento\Framework\Exception\CouldNotSaveException;
use \Magento\Framework\Exception\NoSuchEntityException;
use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Magento\Framework\Api\SortOrder;
use \Gabrielqs\Boleto\Model\Remittance\File\OrderRepository as Subject;
use \Gabrielqs\Boleto\Model\ResourceModel\Remittance\File\Order as RemittanceFileOrderResource;
use \Gabrielqs\Boleto\Model\Remittance\File\OrderFactory as RemittanceFileOrderFactory;
use \Gabrielqs\Boleto\Model\ResourceModel\Remittance\File\Order\CollectionFactory as RemittanceFileOrderCollectionFactory;
use \Gabrielqs\Boleto\Model\ResourceModel\Remittance\File\Order\Collection as RemittanceFileOrderCollection;
use \Gabrielqs\Boleto\Model\Remittance\File\Order as RemittanceFileOrder;
use \Gabrielqs\Boleto\Api\Data\RemittanceFileOrderSearchResultsInterfaceFactory;



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
     * Remittance File Resource
     * @var RemittanceFileOrderResource
     */
    protected $resource;

    /**
     * File Factory
     * @var RemittanceFileOrderFactory
     */
    protected $remittanceFileOrderFactory;

    /**
     * File Collection Factory
     * @var RemittanceFileOrderSearchResultsInterfaceFactory
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

        $this->resource = $this->getMockBuilder(RemittanceFileOrderResource::class)
            ->setMethods(['save', 'load', 'delete'])
            ->disableOriginalConstructor()
            ->getMock();
        $arguments['resource'] = $this->resource;

        $this->remittanceFileOrderFactory = $this->getMockBuilder(RemittanceFileOrderFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $arguments['remittanceFileOrderFactory'] = $this->remittanceFileOrderFactory;

        $this->searchResultsFactory = $this->getMockBuilder(RemittanceFileOrderSearchResultsInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $arguments['searchResultsFactory'] = $this->searchResultsFactory;

        return $arguments;
    }

    public function testSaveRemittanceCouldNotSaveExceptionOnException()
    {
        $remittanceFileOrder = $this->getMockBuilder(RemittanceFileOrder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this
            ->resource
            ->expects($this->once())
            ->method('save')
            ->will($this->throwException(new \Exception()));

        $this->setExpectedException(CouldNotSaveException::class);
        $this->subject->save($remittanceFileOrder);
    }

    public function testSaveRemittanceSuccessfullySavesEntityToResourceAndRemittanceIt()
    {
        $remittanceFileOrder = $this->getMockBuilder(RemittanceFileOrder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this
            ->resource
            ->expects($this->once())
            ->method('save')
            ->will($this->returnValue($remittanceFileOrder));

        $return = $this->subject->save($remittanceFileOrder);

        $this->assertEquals($return, $remittanceFileOrder);
    }

    public function testGetByIdThrowsNoSuchEntityExceptionWhenEntityNotFound()
    {
        $remittanceFileOrder = $this->getMockBuilder(RemittanceFileOrder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $remittanceFileOrder
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(null));

        $this
            ->remittanceFileOrderFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($remittanceFileOrder));

        $this
            ->resource
            ->expects($this->once())
            ->method('load')
            ->with($remittanceFileOrder, 324)
            ->will($this->returnValue($remittanceFileOrder));

        $this->setExpectedException(NoSuchEntityException::class);
        $this->subject->getById(324);

    }

    public function testGetByIdRemittanceLoadedEntityWhenEntityFound()
    {
        $remittanceFileOrder = $this->getMockBuilder(RemittanceFileOrder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $remittanceFileOrder
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(324));

        $this
            ->remittanceFileOrderFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($remittanceFileOrder));

        $this
            ->resource
            ->expects($this->once())
            ->method('load')
            ->with($remittanceFileOrder, 324)
            ->will($this->returnValue($remittanceFileOrder));

        $return = $this->subject->getById(324);

        $this->assertEquals($remittanceFileOrder, $return);

    }
    
    public function testDeleteByIdUsesResourceToDeleteAndRemittanceTrue()
    {
        $remittanceFileOrderId = '123';

        $remittanceFileOrder = $this->getMockBuilder(RemittanceFileOrder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $remittanceFileOrder->expects($this->any())
            ->method('getId')
            ->willReturn($remittanceFileOrderId);

        $this
            ->remittanceFileOrderFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($remittanceFileOrder));

        $this
            ->resource
            ->expects($this->once())
            ->method('load')
            ->with($remittanceFileOrder, $remittanceFileOrderId)
            ->willReturn($remittanceFileOrder);

        $this
            ->resource
            ->expects($this->once())
            ->method('delete')
            ->with($remittanceFileOrder)
            ->willReturnSelf();

        $this->assertTrue($this->subject->deleteById($remittanceFileOrderId));
    }

    public function testDeleteByIdThrowsExceptionWhenEntityNotFound()
    {
        $remittanceFileOrderId = '123';

        $remittanceFileOrder = $this->getMockBuilder(RemittanceFileOrder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $remittanceFileOrder->expects($this->any())
            ->method('getId')
            ->willReturn(false);

        $this
            ->remittanceFileOrderFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($remittanceFileOrder));

        $this
            ->resource
            ->expects($this->once())
            ->method('load')
            ->with($remittanceFileOrder, $remittanceFileOrderId)
            ->will($this->throwException(new NoSuchEntityException()));

        $this
            ->resource
            ->expects($this->never())
            ->method('delete')
            ->willReturnSelf();

        $this->setExpectedException(NoSuchEntityException::class);

        $this->assertTrue($this->subject->deleteById($remittanceFileOrderId));
    }

    public function testDeleteByIdThrowsExceptionWhenCouldNotDelete()
    {
        $remittanceFileOrderId = '123';

        $remittanceFileOrder = $this->getMockBuilder(RemittanceFileOrder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $remittanceFileOrder
            ->expects($this->any())
            ->method('getId')
            ->willReturn($remittanceFileOrderId);

        $this
            ->remittanceFileOrderFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($remittanceFileOrder);

        $this
            ->resource
            ->expects($this->once())
            ->method('load')
            ->with($remittanceFileOrder, $remittanceFileOrderId)
            ->willReturn($remittanceFileOrder);

        $this
            ->resource
            ->expects($this->once())
            ->method('delete')
            ->will($this->throwException(new \Exception()));

        $this->setExpectedException(CouldNotDeleteException::class);

        $this->assertTrue($this->subject->deleteById($remittanceFileOrderId));
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

        $remittanceFileOrderCollection = $this->getMockBuilder(RemittanceFileOrderCollection::class)
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
            ->will($this->returnValue($remittanceFileOrderCollection));

        $remittanceFileOrderCollection
            ->expects($this->once())
            ->method('addFieldToFilter')
            ->with([ 'store_id', 'name' ],
            [
                [ 'eq' => 1 ],
                [ 'eq' => 'magento' ]
            ])
            ->willReturnSelf();

        $remittanceFileOrderCollection->expects($this->once())->method('setCurPage')->with($currentPage)->willReturnSelf();
        $remittanceFileOrderCollection->expects($this->once())->method('setPageSize')->with($pageSize)->willReturnSelf();
        $remittanceFileOrderCollection->expects($this->once())->method('addOrder')->with($sortField, 'DESC')->willReturnSelf();

        $this->assertEquals($remittanceFileOrderCollection, $this->subject->getList($criteria));
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

        $remittanceFileOrderCollection = $this->getMockBuilder(RemittanceFileOrderCollection::class)
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
            ->will($this->returnValue($remittanceFileOrderCollection));

        $remittanceFileOrderCollection
            ->expects($this->once())
            ->method('addFieldToFilter')
            ->with([ 'store_id', 'name' ],
            [
                [ 'eq' => 1 ],
                [ 'eq' => 'magento' ]
            ])
            ->willReturnSelf();

        $remittanceFileOrderCollection->expects($this->once())->method('setCurPage')->with($currentPage)->willReturnSelf();
        $remittanceFileOrderCollection->expects($this->once())->method('setPageSize')->with($pageSize)->willReturnSelf();

        $this->assertEquals($remittanceFileOrderCollection, $this->subject->getList($criteria));
    }

}