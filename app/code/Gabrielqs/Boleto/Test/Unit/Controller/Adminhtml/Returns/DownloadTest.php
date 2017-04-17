<?php

namespace Gabrielqs\Boleto\Test\Unit\Controller\Adminhtml\Returns;

use \Magento\Framework\DataObject;
use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Magento\Backend\App\Action\Context;
use \Magento\Framework\Message\Manager as MessageManager;
use \Magento\Framework\App\Response\Http\FileFactory;
use \Gabrielqs\Boleto\Controller\Adminhtml\Returns\Download as Subject;
use \Gabrielqs\Boleto\Model\Returns\FileRepository as ReturnsFileRepository;
use \Gabrielqs\Boleto\Model\Returns\File;

/**
 * Unit Testcase
 */
class DownloadTest extends \PHPUnit_Framework_TestCase
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
     * @var FileFactory
     */
    protected $fileFactory;

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
            ->setMethods(['getById'])
            ->getMock();
        $arguments['returnsFileRepository'] = $this->returnsFileRepository;

        $this->fileFactory = $this->getMockBuilder(FileFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $arguments['fileFactory'] = $this->fileFactory;


        return $arguments;
    }

    public function testExecuteAddsErrorAndReturnsToIndexWhenNoIdInformed()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('_getReturnsFileIdFromRequest')
            ->will($this->returnValue(48));

        $this
            ->returnsFileRepository
            ->expects($this->once())
            ->method('getById')
            ->with(48)
            ->will($this->throwException(new \Exception('Foo Bar')));

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

    public function testExecuteLoadsFileWithRepositoryReturnsDownloadWithRightContents()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('_getReturnsFileIdFromRequest')
            ->will($this->returnValue(1039));

        $returnsFile = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->setMethods(['getName', 'getContents'])
            ->getMock();

        $returnsFile
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('CN329192A.REM'));

        $returnsFile
            ->expects($this->once())
            ->method('getContents')
            ->will($this->returnValue('FOO BAR BAZ CONTENTS'));

        $this
            ->returnsFileRepository
            ->expects($this->once())
            ->method('getById')
            ->with(1039)
            ->will($this->returnValue($returnsFile));

        $this
            ->fileFactory
            ->expects($this->once())
            ->method('create')
            ->with('CN329192A.REM', 'FOO BAR BAZ CONTENTS')
            ->will($this->returnValue('File Ready for Download'));

        $return = $this->subject->execute();

        $this->assertEquals('File Ready for Download', $return);
    }
}