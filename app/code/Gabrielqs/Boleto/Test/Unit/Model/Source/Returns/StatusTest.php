<?php

namespace Gabrielqs\Boleto\Test\Unit\Model\Source\Returns;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Gabrielqs\Boleto\Model\Source\Returns\Status as Subject;
use \Gabrielqs\Boleto\Helper\Returns\Data as ReturnsHelper;
use \Gabrielqs\Boleto\Model\Returns\File as ReturnsFile;

/**
 * Unit Testcase
 */
class StatusTest extends \PHPUnit_Framework_TestCase
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
     * Returns Helper
     * @var ReturnsHelper
     */
    protected $returnsHelper;

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

        $this->returnsHelper = $this->getMockBuilder(ReturnsHelper::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $arguments['helper'] = $this->returnsHelper;

        return $arguments;
    }

    public function testToOptionArrayReturnsTheCorrectValues()
    {
        $expectedReturn = [
            [
                'label' => 'New',
                'value' => ReturnsFile::STATUS_NEW
            ],
            [
                'label' => 'Error',
                'value' => ReturnsFile::STATUS_ERROR
            ],
            [
                'label' => 'Success',
                'value' => ReturnsFile::STATUS_SUCCESS
            ]
        ];

        $return = $this->subject->toOptionArray();
        $this->assertEquals($expectedReturn, $return);
    }


}