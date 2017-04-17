<?php

namespace Gabrielqs\Boleto\Test\Unit\Model\Source\Remittance;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Gabrielqs\Boleto\Model\Source\Remittance\Status as Subject;
use \Gabrielqs\Boleto\Helper\Remittance\Data as RemittanceHelper;
use \Gabrielqs\Boleto\Model\Remittance\File as RemittanceFile;

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
     * Remittance Helper
     * @var RemittanceHelper
     */
    protected $RemittanceHelper;

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

        $this->RemittanceHelper = $this->getMockBuilder(RemittanceHelper::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $arguments['helper'] = $this->RemittanceHelper;

        return $arguments;
    }

    public function testToOptionArrayRemittanceTheCorrectValues()
    {
        $expectedReturn = [
            [
                'label' => 'New',
                'value' => RemittanceFile::STATUS_NEW
            ],
            [
                'label' => 'Error',
                'value' => RemittanceFile::STATUS_ERROR
            ],
            [
                'label' => 'Success',
                'value' => RemittanceFile::STATUS_SUCCESS
            ]
        ];

        $return = $this->subject->toOptionArray();
        $this->assertEquals($expectedReturn, $return);
    }


}