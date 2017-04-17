<?php

namespace Gabrielqs\Boleto\Test\Unit\Controller\Adminhtml\Returns;

use \Magento\Framework\DataObject;
use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Magento\Backend\App\Action\Context;
use \Magento\Framework\Message\Manager as MessageManager;
use \Gabrielqs\Boleto\Controller\Adminhtml\Returns\Delete as Subject;
use \Gabrielqs\Boleto\Model\Returns\FileRepository as ReturnsFileRepository;

/**
 * Unit Testcase
 */
class DeleteTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var String
     */
    protected $className = null;

    /**
     * @var Context
     */
    protected $context = null;

    /**
     * @var MessageManager
     */
    protected $messageManager = null;

    /**
     * @var ObjectManager
     */
    protected $objectManager = null;

    /**
     * @var Subject
     */
    protected $originalSubject = null;

    /**
     * @var ReturnsFileRepository;
     */
    protected $returnsFileRepository = null;

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
            ->setMethods(['_getReturnsFileIdFromRequest', '_getResultRedirect'])
            ->getMock();

        $this->originalSubject = $this
            ->objectManager
            ->getObject($this->className, $arguments);
    }

    protected function getConstructorArguments()
    {
        $arguments = $this->objectManager->getConstructArguments($this->className);

        $this->messageManager = $this->getMockBuilder(MessageManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['addSuccessMessage', 'addErrorMessage'])
            ->getMock();
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMessageManager'])
            ->getMock();
        $this->context
            ->expects($this->any())
            ->method('getMessageManager')
            ->will($this->returnValue(($this->messageManager)));
        $arguments['context'] = $this->context;

        $this->returnsFileRepository = $this->getMockBuilder(ReturnsFileRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['deleteById'])
            ->getMock();
        $arguments['returnsFileRepository'] = $this->returnsFileRepository;
        return $arguments;
    }

    public function testExecuteAddsErrorAndReturnsToIndexWhenNoIdInformed()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('_getReturnsFileIdFromRequest')
            ->will($this->returnValue(null));

        $this
            ->messageManager
            ->expects($this->once())
            ->method('addErrorMessage');

        $resultRedirect = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['setPath'])
            ->getMock();

        $resultRedirect
            ->expects($this->once())
            ->method('setPath')
            ->with('boleto/returns/index');

        $this
            ->subject
            ->expects($this->once())
            ->method('_getResultRedirect')
            ->will($this->returnValue($resultRedirect));

        $this->subject->execute();
    }

    public function testExecuteDeletesFileWithRepositoryAddsSuccessAndRedirectsToIndex()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('_getReturnsFileIdFromRequest')
            ->will($this->returnValue(15));

        $this
            ->returnsFileRepository
            ->expects($this->once())
            ->method('deleteById')
            ->with(15);

        $this
            ->messageManager
            ->expects($this->once())
            ->method('addSuccessMessage');

        $resultRedirect = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['setPath'])
            ->getMock();

        $resultRedirect
            ->expects($this->once())
            ->method('setPath')
            ->with('boleto/returns/index');

        $this
            ->subject
            ->expects($this->once())
            ->method('_getResultRedirect')
            ->will($this->returnValue($resultRedirect));

        $this->subject->execute();
    }

    public function testExecuteDeleteCatchesExceptionAddsErrorAndRedirectsToIndex()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('_getReturnsFileIdFromRequest')
            ->will($this->returnValue(15));

        $this
            ->returnsFileRepository
            ->expects($this->once())
            ->method('deleteById')
            ->with(15)
            ->will($this->throwException(new \Exception('Foo')));

        $this
            ->messageManager
            ->expects($this->once())
            ->method('addErrorMessage');

        $resultRedirect = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['setPath'])
            ->getMock();

        $resultRedirect
            ->expects($this->once())
            ->method('setPath')
            ->with('boleto/returns/index');

        $this
            ->subject
            ->expects($this->once())
            ->method('_getResultRedirect')
            ->will($this->returnValue($resultRedirect));

        $this->subject->execute();
    }
}