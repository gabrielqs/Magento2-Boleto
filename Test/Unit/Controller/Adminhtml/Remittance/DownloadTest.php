<?php

namespace Gabrielqs\Boleto\Test\Unit\Controller\Adminhtml\Remittance;

use \Magento\Framework\DataObject;
use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Magento\Backend\App\Action\Context;
use \Magento\Framework\Message\Manager as MessageManager;
use \Magento\Framework\App\Response\Http\FileFactory;
use \Gabrielqs\Boleto\Controller\Adminhtml\Remittance\Download as Subject;
use \Gabrielqs\Boleto\Model\Remittance\FileRepository as RemittanceFileRepository;
use \Gabrielqs\Boleto\Model\Remittance\File;

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
     * @var RemittanceFileRepository;
     */
    protected $remittanceFileRepository = null;

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
            ->setMethods(['_getRemittanceFileIdFromRequest', '_getResultRedirect'])
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

        $this->remittanceFileRepository = $this->getMockBuilder(RemittanceFileRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getById'])
            ->getMock();
        $arguments['remittanceFileRepository'] = $this->remittanceFileRepository;

        $this->fileFactory = $this->getMockBuilder(FileFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $arguments['fileFactory'] = $this->fileFactory;


        return $arguments;
    }

    public function testExecuteAddsErrorAndRemittanceToIndexWhenNoIdInformed()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('_getRemittanceFileIdFromRequest')
            ->will($this->returnValue(48));

        $this
            ->remittanceFileRepository
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
            ->with('boleto/remittance/index');

        $this
            ->subject
            ->expects($this->once())
            ->method('_getResultRedirect')
            ->will($this->returnValue($resultRedirect));

        $this->subject->execute();
    }

    public function testExecuteLoadsFileWithRepositoryRemittanceDownloadWithRightContents()
    {
        $this
            ->subject
            ->expects($this->once())
            ->method('_getRemittanceFileIdFromRequest')
            ->will($this->returnValue(1039));

        $remittanceFile = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->setMethods(['getName', 'getContents'])
            ->getMock();

        $remittanceFile
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('CN329192A.REM'));

        $remittanceFile
            ->expects($this->once())
            ->method('getContents')
            ->will($this->returnValue('FOO BAR BAZ CONTENTS'));

        $this
            ->remittanceFileRepository
            ->expects($this->once())
            ->method('getById')
            ->with(1039)
            ->will($this->returnValue($remittanceFile));

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