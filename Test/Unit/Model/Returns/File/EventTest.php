<?php

namespace Gabrielqs\Boleto\Test\Unit\Model\Returns\File\Event;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Gabrielqs\Boleto\Model\Returns\File\Event as Subject;

/**
 * Unit Testcase
 */
class EventTest extends \PHPUnit_Framework_TestCase
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

    public function testGetDescriptionGetsTheRightKey()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('getData')
            ->with(Subject::DESCRIPTION);
        $this->subject->getDescription();
    }

    public function testSetDescriptionSetsTheRightKey()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('setData')
            ->with(Subject::DESCRIPTION, 'foobar');
        $this->subject->setDescription('foobar');
    }

    public function testGetIdGetsTheRightKey()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('getData')
            ->with(Subject::RETURNS_FILE_EVENT_ID);
        $this->subject->getId();
    }

    public function testSetIdSetsTheRightKey()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('setData')
            ->with(Subject::RETURNS_FILE_EVENT_ID, 'foobar');
        $this->subject->setId('foobar');
    }

    public function testGetIdentitiesReturnsTheRightValue()
    {
        $expectedReturn = [Subject::CACHE_TAG . '_2'];
        $this
            ->originalSubject
            ->setId(2);
        $return = $this->originalSubject->getIdentities();
        $this->assertEquals($expectedReturn, $return);
    }

    public function testGetReturnsFileIdGetsTheRightKey()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('getData')
            ->with(Subject::RETURNS_FILE_ID);
        $this->subject->getReturnsFileId();
    }

    public function testSetReturnsFileIdSetsTheRightKey()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('setData')
            ->with(Subject::RETURNS_FILE_ID, 'foobar');
        $this->subject->setReturnsFileId('foobar');
    }

}