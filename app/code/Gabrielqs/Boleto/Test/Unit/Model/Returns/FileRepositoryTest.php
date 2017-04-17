<?php

namespace Gabrielqs\Boleto\Test\Unit\Model\Returns;

use \Magento\Framework\Exception\CouldNotDeleteException;
use \Magento\Framework\Exception\CouldNotSaveException;
use \Magento\Framework\Exception\NoSuchEntityException;
use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Magento\Framework\Api\SortOrder;
use \Gabrielqs\Boleto\Model\Returns\FileRepository as Subject;
use \Gabrielqs\Boleto\Model\ResourceModel\Returns\File as ReturnsFileResource;
use \Gabrielqs\Boleto\Model\Returns\FileFactory as ReturnsFileFactory;
use \Gabrielqs\Boleto\Model\ResourceModel\Returns\File\CollectionFactory as ReturnsFileCollectionFactory;
use \Gabrielqs\Boleto\Model\ResourceModel\Returns\File\Collection as ReturnsFileCollection;
use \Gabrielqs\Boleto\Model\Returns\File as ReturnsFile;
use \Gabrielqs\Boleto\Api\Data\ReturnsFileSearchResultsInterfaceFactory;



/**
 * Unit Testcase
 */
class FileRepositoryTest extends \PHPUnit_Framework_TestCase
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
     * @var ReturnsFileResource
     */
    protected $resource;

    /**
     * File Factory
     * @var ReturnsFileFactory
     */
    protected $returnsFileFactory;

    /**
     * File Collection Factory
     * @var ReturnsFileSearchResultsInterfaceFactory
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

        $this->resource = $this->getMockBuilder(ReturnsFileResource::class)
            ->setMethods(['save', 'load', 'delete'])
            ->disableOriginalConstructor()
            ->getMock();
        $arguments['resource'] = $this->resource;

        $this->returnsFileFactory = $this->getMockBuilder(ReturnsFileFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $arguments['returnsFileFactory'] = $this->returnsFileFactory;

        $this->searchResultsFactory = $this->getMockBuilder(ReturnsFileSearchResultsInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $arguments['searchResultsFactory'] = $this->searchResultsFactory;

        return $arguments;
    }

    public function testSaveReturnsCouldNotSaveExceptionOnException()
    {
        $returnsFile = $this->getMockBuilder(ReturnsFile::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this
            ->resource
            ->expects($this->once())
            ->method('save')
            ->will($this->throwException(new \Exception()));

        $this->setExpectedException(CouldNotSaveException::class);
        $this->subject->save($returnsFile);
    }

    public function testSaveReturnsSuccessfullySavesEntityToResourceAndReturnsIt()
    {
        $returnsFile = $this->getMockBuilder(ReturnsFile::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this
            ->resource
            ->expects($this->once())
            ->method('save')
            ->will($this->returnValue($returnsFile));

        $return = $this->subject->save($returnsFile);

        $this->assertEquals($return, $returnsFile);
    }

    public function testGetByIdThrowsNoSuchEntityExceptionWhenEntityNotFound()
    {
        $returnsFile = $this->getMockBuilder(ReturnsFile::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $returnsFile
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(null));

        $this
            ->returnsFileFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($returnsFile));

        $this
            ->resource
            ->expects($this->once())
            ->method('load')
            ->with($returnsFile, 324)
            ->will($this->returnValue($returnsFile));

        $this->setExpectedException(NoSuchEntityException::class);
        $this->subject->getById(324);

    }

    public function testGetByIdReturnsLoadedEntityWhenEntityFound()
    {
        $returnsFile = $this->getMockBuilder(ReturnsFile::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $returnsFile
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(324));

        $this
            ->returnsFileFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($returnsFile));

        $this
            ->resource
            ->expects($this->once())
            ->method('load')
            ->with($returnsFile, 324)
            ->will($this->returnValue($returnsFile));

        $return = $this->subject->getById(324);

        $this->assertEquals($returnsFile, $return);

    }
    
    public function testDeleteByIdUsesResourceToDeleteAndReturnsTrue()
    {
        $returnsFileId = '123';

        $returnsFile = $this->getMockBuilder(ReturnsFile::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $returnsFile->expects($this->any())
            ->method('getId')
            ->willReturn($returnsFileId);

        $this
            ->returnsFileFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($returnsFile));

        $this
            ->resource
            ->expects($this->once())
            ->method('load')
            ->with($returnsFile, $returnsFileId)
            ->willReturn($returnsFile);

        $this
            ->resource
            ->expects($this->once())
            ->method('delete')
            ->with($returnsFile)
            ->willReturnSelf();

        $this->assertTrue($this->subject->deleteById($returnsFileId));
    }

    public function testDeleteByIdThrowsExceptionWhenEntityNotFound()
    {
        $returnsFileId = '123';

        $returnsFile = $this->getMockBuilder(ReturnsFile::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $returnsFile->expects($this->any())
            ->method('getId')
            ->willReturn(false);

        $this
            ->returnsFileFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($returnsFile));

        $this
            ->resource
            ->expects($this->once())
            ->method('load')
            ->with($returnsFile, $returnsFileId)
            ->will($this->throwException(new NoSuchEntityException()));

        $this
            ->resource
            ->expects($this->never())
            ->method('delete')
            ->willReturnSelf();

        $this->setExpectedException(NoSuchEntityException::class);

        $this->assertTrue($this->subject->deleteById($returnsFileId));
    }

    public function testDeleteByIdThrowsExceptionWhenCouldNotDelete()
    {
        $returnsFileId = '123';

        $returnsFile = $this->getMockBuilder(ReturnsFile::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $returnsFile
            ->expects($this->any())
            ->method('getId')
            ->willReturn($returnsFileId);

        $this
            ->returnsFileFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($returnsFile);

        $this
            ->resource
            ->expects($this->once())
            ->method('load')
            ->with($returnsFile, $returnsFileId)
            ->willReturn($returnsFile);

        $this
            ->resource
            ->expects($this->once())
            ->method('delete')
            ->will($this->throwException(new \Exception()));

        $this->setExpectedException(CouldNotDeleteException::class);

        $this->assertTrue($this->subject->deleteById($returnsFileId));
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

        $returnsFileCollection = $this->getMockBuilder(ReturnsFileCollection::class)
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
            ->will($this->returnValue($returnsFileCollection));

        $returnsFileCollection
            ->expects($this->once())
            ->method('addFieldToFilter')
            ->with([ 'store_id', 'name' ],
            [
                [ 'eq' => 1 ],
                [ 'eq' => 'magento' ]
            ])
            ->willReturnSelf();

        $returnsFileCollection->expects($this->once())->method('setCurPage')->with($currentPage)->willReturnSelf();
        $returnsFileCollection->expects($this->once())->method('setPageSize')->with($pageSize)->willReturnSelf();
        $returnsFileCollection->expects($this->once())->method('addOrder')->with($sortField, 'DESC')->willReturnSelf();

        $this->assertEquals($returnsFileCollection, $this->subject->getList($criteria));
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

        $returnsFileCollection = $this->getMockBuilder(ReturnsFileCollection::class)
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
            ->will($this->returnValue($returnsFileCollection));

        $returnsFileCollection
            ->expects($this->once())
            ->method('addFieldToFilter')
            ->with([ 'store_id', 'name' ],
            [
                [ 'eq' => 1 ],
                [ 'eq' => 'magento' ]
            ])
            ->willReturnSelf();

        $returnsFileCollection->expects($this->once())->method('setCurPage')->with($currentPage)->willReturnSelf();
        $returnsFileCollection->expects($this->once())->method('setPageSize')->with($pageSize)->willReturnSelf();

        $this->assertEquals($returnsFileCollection, $this->subject->getList($criteria));
    }

}