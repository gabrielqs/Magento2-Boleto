<?php

namespace Gabrielqs\Boleto\Test\Unit\Helper\Remittance;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Gabrielqs\Boleto\Helper\Remittance\Data as Subject;
use \Gabrielqs\Boleto\Model\Remittance\File as RemittanceFile;

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

    public function testGetAllRemittanceFileStatus()
    {
        $return = $this->subject->getAllRemittanceFileStatus();
        $this->assertInternalType('array', $return);
        $this->assertArrayHasKey(RemittanceFile::STATUS_NEW, $return);
        $this->assertArrayHasKey(RemittanceFile::STATUS_ERROR, $return);
        $this->assertArrayHasKey(RemittanceFile::STATUS_SUCCESS, $return);
    }

    public function dataProvidertestGetStatusLabel()
    {
        return [
            [RemittanceFile::STATUS_NEW, 'New'],
            [RemittanceFile::STATUS_ERROR, 'Error'],
            [RemittanceFile::STATUS_SUCCESS, 'Success']
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