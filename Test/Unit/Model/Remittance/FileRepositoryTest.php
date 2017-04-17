<?php

namespace Gabrielqs\Boleto\Test\Unit\Model\Remittance;

use \Magento\Framework\Exception\CouldNotDeleteException;
use \Magento\Framework\Exception\CouldNotSaveException;
use \Magento\Framework\Exception\NoSuchEntityException;
use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Magento\Framework\Api\SortOrder;
use \Gabrielqs\Boleto\Model\Remittance\FileRepository as Subject;
use \Gabrielqs\Boleto\Model\ResourceModel\Remittance\File as RemittanceFileResource;
use \Gabrielqs\Boleto\Model\Remittance\FileFactory as RemittanceFileFactory;
use \Gabrielqs\Boleto\Model\ResourceModel\Remittance\File\CollectionFactory as RemittanceFileCollectionFactory;
use \Gabrielqs\Boleto\Model\ResourceModel\Remittance\File\Collection as RemittanceFileCollection;
use \Gabrielqs\Boleto\Model\Remittance\File as RemittanceFile;
use \Gabrielqs\Boleto\Api\Data\RemittanceFileSearchResultsInterfaceFactory;



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
     * Remittance File Resource
     * @var RemittanceFileResource
     */
    protected $resource;

    /**
     * File Factory
     * @var RemittanceFileFactory
     */
    protected $remittanceFileFactory;

    /**
     * File Collection Factory
     * @var RemittanceFileSearchResultsInterfaceFactory
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

        $this->resource = $this->getMockBuilder(RemittanceFileResource::class)
            ->setMethods(['save', 'load', 'delete'])
            ->disableOriginalConstructor()
            ->getMock();
        $arguments['resource'] = $this->resource;

        $this->remittanceFileFactory = $this->getMockBuilder(RemittanceFileFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $arguments['remittanceFileFactory'] = $this->remittanceFileFactory;

        $this->searchResultsFactory = $this->getMockBuilder(RemittanceFileSearchResultsInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $arguments['searchResultsFactory'] = $this->searchResultsFactory;

        return $arguments;
    }

    public function testSaveRemittanceCouldNotSaveExceptionOnException()
    {
        $remittanceFile = $this->getMockBuilder(RemittanceFile::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this
            ->resource
            ->expects($this->once())
            ->method('save')
            ->will($this->throwException(new \Exception()));

        $this->setExpectedException(CouldNotSaveException::class);
        $this->subject->save($remittanceFile);
    }

    public function testSaveRemittanceSuccessfullySavesEntityToResourceAndRemittanceIt()
    {
        $remittanceFile = $this->getMockBuilder(RemittanceFile::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this
            ->resource
            ->expects($this->once())
            ->method('save')
            ->will($this->returnValue($remittanceFile));

        $return = $this->subject->save($remittanceFile);

        $this->assertEquals($return, $remittanceFile);
    }

    public function testGetByIdThrowsNoSuchEntityExceptionWhenEntityNotFound()
    {
        $remittanceFile = $this->getMockBuilder(RemittanceFile::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $remittanceFile
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(null));

        $this
            ->remittanceFileFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($remittanceFile));

        $this
            ->resource
            ->expects($this->once())
            ->method('load')
            ->with($remittanceFile, 324)
            ->will($this->returnValue($remittanceFile));

        $this->setExpectedException(NoSuchEntityException::class);
        $this->subject->getById(324);

    }

    public function testGetByIdRemittanceLoadedEntityWhenEntityFound()
    {
        $remittanceFile = $this->getMockBuilder(RemittanceFile::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $remittanceFile
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(324));

        $this
            ->remittanceFileFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($remittanceFile));

        $this
            ->resource
            ->expects($this->once())
            ->method('load')
            ->with($remittanceFile, 324)
            ->will($this->returnValue($remittanceFile));

        $return = $this->subject->getById(324);

        $this->assertEquals($remittanceFile, $return);

    }
    
    public function testDeleteByIdUsesResourceToDeleteAndRemittanceTrue()
    {
        $remittanceFileId = '123';

        $remittanceFile = $this->getMockBuilder(RemittanceFile::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $remittanceFile->expects($this->any())
            ->method('getId')
            ->willReturn($remittanceFileId);

        $this
            ->remittanceFileFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($remittanceFile));

        $this
            ->resource
            ->expects($this->once())
            ->method('load')
            ->with($remittanceFile, $remittanceFileId)
            ->willReturn($remittanceFile);

        $this
            ->resource
            ->expects($this->once())
            ->method('delete')
            ->with($remittanceFile)
            ->willReturnSelf();

        $this->assertTrue($this->subject->deleteById($remittanceFileId));
    }

    public function testDeleteByIdThrowsExceptionWhenEntityNotFound()
    {
        $remittanceFileId = '123';

        $remittanceFile = $this->getMockBuilder(RemittanceFile::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $remittanceFile->expects($this->any())
            ->method('getId')
            ->willReturn(false);

        $this
            ->remittanceFileFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($remittanceFile));

        $this
            ->resource
            ->expects($this->once())
            ->method('load')
            ->with($remittanceFile, $remittanceFileId)
            ->will($this->throwException(new NoSuchEntityException()));

        $this
            ->resource
            ->expects($this->never())
            ->method('delete')
            ->willReturnSelf();

        $this->setExpectedException(NoSuchEntityException::class);

        $this->assertTrue($this->subject->deleteById($remittanceFileId));
    }

    public function testDeleteByIdThrowsExceptionWhenCouldNotDelete()
    {
        $remittanceFileId = '123';

        $remittanceFile = $this->getMockBuilder(RemittanceFile::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $remittanceFile
            ->expects($this->any())
            ->method('getId')
            ->willReturn($remittanceFileId);

        $this
            ->remittanceFileFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($remittanceFile);

        $this
            ->resource
            ->expects($this->once())
            ->method('load')
            ->with($remittanceFile, $remittanceFileId)
            ->willReturn($remittanceFile);

        $this
            ->resource
            ->expects($this->once())
            ->method('delete')
            ->will($this->throwException(new \Exception()));

        $this->setExpectedException(CouldNotDeleteException::class);

        $this->assertTrue($this->subject->deleteById($remittanceFileId));
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

        $remittanceFileCollection = $this->getMockBuilder(RemittanceFileCollection::class)
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
            ->will($this->returnValue($remittanceFileCollection));

        $remittanceFileCollection
            ->expects($this->once())
            ->method('addFieldToFilter')
            ->with([ 'store_id', 'name' ],
            [
                [ 'eq' => 1 ],
                [ 'eq' => 'magento' ]
            ])
            ->willReturnSelf();

        $remittanceFileCollection->expects($this->once())->method('setCurPage')->with($currentPage)->willReturnSelf();
        $remittanceFileCollection->expects($this->once())->method('setPageSize')->with($pageSize)->willReturnSelf();
        $remittanceFileCollection->expects($this->once())->method('addOrder')->with($sortField, 'DESC')->willReturnSelf();

        $this->assertEquals($remittanceFileCollection, $this->subject->getList($criteria));
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

        $remittanceFileCollection = $this->getMockBuilder(RemittanceFileCollection::class)
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
            ->will($this->returnValue($remittanceFileCollection));

        $remittanceFileCollection
            ->expects($this->once())
            ->method('addFieldToFilter')
            ->with([ 'store_id', 'name' ],
            [
                [ 'eq' => 1 ],
                [ 'eq' => 'magento' ]
            ])
            ->willReturnSelf();

        $remittanceFileCollection->expects($this->once())->method('setCurPage')->with($currentPage)->willReturnSelf();
        $remittanceFileCollection->expects($this->once())->method('setPageSize')->with($pageSize)->willReturnSelf();

        $this->assertEquals($remittanceFileCollection, $this->subject->getList($criteria));
    }

}