<?php

namespace Gabrielqs\Boleto\Test\Unit\Block\Adminhtml\Returns\Upload;

use Magento\Framework\Registry;
use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Gabrielqs\Boleto\Block\Adminhtml\Returns\Upload\GenericButton as Subject;
use \Gabrielqs\Boleto\Controller\Adminhtml\RegistryConstants;

/**
 * Unit Testcase
 */
class GenericButtonTest extends \PHPUnit_Framework_TestCase
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
     * @var Registry
     */
    protected $registry = null;

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

        $this->registry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->setMethods(['registry'])
            ->getMock();
        $arguments['coreRegistry'] = $this->registry;


        return $arguments;
    }

    public function testGetReturnsFileIdGetsItFromTheRegistryWithTheRightKey()
    {
        $this
            ->registry
            ->expects($this->once())
            ->method('registry')
            ->with(RegistryConstants::CURRENT_RETURNS_FILE_ID)
            ->will($this->returnValue('214'));
        $return = $this->subject->getReturnsFileId();
        $this->assertEquals(214, $return);

    }
}