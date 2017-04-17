<?php

namespace Gabrielqs\Boleto\Test\Unit\Block\Adminhtml\Returns\Upload;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Gabrielqs\Boleto\Block\Adminhtml\Returns\Upload\UploadButton as Subject;

/**
 * Unit Testcase
 */
class UploadButtonTest extends \PHPUnit_Framework_TestCase
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
            ->setMethods(['getUrl'])
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

    public function testGetButtonData()
    {
        $return = $this->subject->getButtonData();
        $this->assertInternalType('array', $return);
        $this->assertArrayHasKey('label', $return);
        $this->assertArrayHasKey('data_attribute', $return);
        $this->assertArrayHasKey('class', $return);
        $this->assertEquals($return['label'], 'Upload Return File');
        $this->assertEquals($return['data_attribute'], [
            'mage-init' => ['button' => ['event' => 'save']],
            'form-role' => 'save',
        ]);

    }
}