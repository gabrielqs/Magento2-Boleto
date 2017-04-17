<?php

namespace Gabrielqs\Boleto\Test\Unit\Helper\Returns;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Gabrielqs\Boleto\Helper\Returns\Data as Subject;
use \Gabrielqs\Boleto\Model\Returns\File as ReturnsFile;

/**
 * Unit Testcase
 */
class DataTest extends \PHPUnit_Framework_TestCase
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
            ->setMethods(null)
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

    public function testGetAllReturnsFileStatus()
    {
        $return = $this->subject->getAllReturnsFileStatus();
        $this->assertInternalType('array', $return);
        $this->assertArrayHasKey(ReturnsFile::STATUS_NEW, $return);
        $this->assertArrayHasKey(ReturnsFile::STATUS_ERROR, $return);
        $this->assertArrayHasKey(ReturnsFile::STATUS_SUCCESS, $return);
    }

    public function dataProvidertestGetStatusLabel()
    {
        return [
            [ReturnsFile::STATUS_NEW, 'New'],
            [ReturnsFile::STATUS_ERROR, 'Error'],
            [ReturnsFile::STATUS_SUCCESS, 'Success']
        ];
    }

    /**
     * @param $status
     * @param $label
     * @dataProvider dataProvidertestGetStatusLabel
     */
    public function testGetStatusLabel($status, $label)
    {
        $return = $this->subject->getStatusLabel($status);
        $this->assertEquals($label, $return);
    }
}