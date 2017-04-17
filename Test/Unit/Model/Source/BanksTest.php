<?php

namespace Gabrielqs\Boleto\Test\Unit\Model\Source;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Gabrielqs\Boleto\Model\Source\Banks as Subject;

/**
 * Unit Testcase
 */
class BanksTest extends \PHPUnit_Framework_TestCase
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

    public function testToOptionArrayReturnsExpectedValue()
    {
        $return = $this->subject->toOptionArray();
        $this->assertInternalType('array', $return);
        $this->assertEquals(2, count($return));
        $this->assertEquals([
            ['value' => Subject::BANK_CODE_ITAU, 'label' => 'Itaú'],
            ['value' => Subject::BANK_CODE_BRADESCO, 'label' => 'Bradesco']
        ], $return);
    }
}