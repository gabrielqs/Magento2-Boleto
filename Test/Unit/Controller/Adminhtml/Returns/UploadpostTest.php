<?php

namespace Gabrielqs\Boleto\Test\Unit\Controller\Adminhtml\Returns;

use \Magento\Framework\DataObject;
use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Magento\Backend\App\Action\Context;
use \Magento\Framework\Message\Manager as MessageManager;
use \Gabrielqs\Boleto\Controller\Adminhtml\Returns\Uploadpost as Subject;
use \Magento\MediaStorage\Model\File\UploaderFactory;
use \Magento\MediaStorage\Model\File\Uploader;
use \Gabrielqs\Boleto\Helper\Returns\Reader as ReturnsFileReader;

/**
 * Unit Testcase
 */
class UploadpostTest extends \PHPUnit_Framework_TestCase
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
     * @var ReturnsFileReader;
     */
    protected $returnsFileReader = null;

    /**
     * @var Subject
     */
    protected $subject = null;

    /**
     * @var Uploader
     */
    protected $uploader;

    /**
     * @var UploaderFactory
     */
    protected $uploaderFactory;

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

        $this->returnsFileReader = $this->getMockBuilder(ReturnsFileReader::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDestinationPath', 'returnsFileExists', 'readAndSaveReturnsFile'])
            ->getMock();
        $arguments['returnsFileReader'] = $this->returnsFileReader;

        $this->uploaderFactory = $this->getMockBuilder(UploaderFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $arguments['uploaderFactory'] = $this->uploaderFactory;

        $this->uploader = $this->getMockBuilder(Uploader::class)
            ->disableOriginalConstructor()
            ->setMethods(['save', 'getUploadedFileName', 'setAllowCreateFolders',
                'setAllowedExtensions', 'addValidateCallback'])
            ->getMock();
        $arguments['uploader'] = $this->uploader;


        return $arguments;
    }

    public function testExecuteAddsErrorMessageAndRedirectsToFormWhenFileIsntWrittenToFileSystem()
    {
        $this
            ->returnsFileReader
            ->expects($this->once())
            ->method('getDestinationPath')
            ->will($this->returnValue('/foo/bar'));

        $this
            ->uploader
            ->expects($this->once())
            ->method('setAllowedExtensions')
            ->with(['ret', 'txt'])
            ->will($this->returnValue($this->uploader));

        $this
            ->uploader
            ->expects($this->once())
            ->method('setAllowCreateFolders')
            ->with(true)
            ->will($this->returnValue($this->uploader));

        $this
            ->uploader
            ->expects($this->once())
            ->method('addValidateCallback')
            ->with('validate', $this->returnsFileReader, 'validateReturnsFile')
            ->will($this->returnValue($this->uploader));

        $this
            ->uploader
            ->expects($this->once())
            ->method('save')
            ->with('/foo/bar')
            ->will($this->throwException(new \Exception('Foo Exception')));

        $this
            ->uploaderFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->uploader));

        $resultRedirect = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['setPath'])
            ->getMock();

        $resultRedirect
            ->expects($this->once())
            ->method('setPath')
            ->with('boleto/returns/uploadform')
            ->will($this->returnValue($resultRedirect));

        $this
            ->subject
            ->expects($this->once())
            ->method('_getResultRedirect')
            ->will($this->returnValue($resultRedirect));

        $this
            ->messageManager
            ->expects($this->once())
            ->method('addErrorMessage');

        $return = $this->subject->execute();

        $this->assertEquals($resultRedirect, $return);
    }

    public function testExecuteAddsErrorMessageAndRedirectsToFormWhenFileIsntWrittenToFileSystemVariation()
    {
        $this
            ->returnsFileReader
            ->expects($this->once())
            ->method('getDestinationPath')
            ->will($this->returnValue('/foo/bar'));

        $this
            ->uploader
            ->expects($this->once())
            ->method('setAllowedExtensions')
            ->with(['ret', 'txt'])
            ->will($this->returnValue($this->uploader));

        $this
            ->uploader
            ->expects($this->once())
            ->method('setAllowCreateFolders')
            ->with(true)
            ->will($this->returnValue($this->uploader));

        $this
            ->uploader
            ->expects($this->once())
            ->method('addValidateCallback')
            ->with('validate', $this->returnsFileReader, 'validateReturnsFile')
            ->will($this->returnValue($this->uploader));

        $this
            ->uploader
            ->expects($this->once())
            ->method('save')
            ->with('/foo/bar')
            ->will($this->returnValue(false));

        $this
            ->uploaderFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->uploader));

        $resultRedirect = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['setPath'])
            ->getMock();

        $resultRedirect
            ->expects($this->once())
            ->method('setPath')
            ->with('boleto/returns/uploadform')
            ->will($this->returnValue($resultRedirect));

        $this
            ->subject
            ->expects($this->once())
            ->method('_getResultRedirect')
            ->will($this->returnValue($resultRedirect));

        $this
            ->messageManager
            ->expects($this->once())
            ->method('addErrorMessage');

        $return = $this->subject->execute();

        $this->assertEquals($resultRedirect, $return);
    }

    public function testExecuteAddsErrorMessageAndRedirectsToFormWhenCantSaveReturnsFile()
    {
        $this
            ->returnsFileReader
            ->expects($this->once())
            ->method('getDestinationPath')
            ->will($this->returnValue('/foo/bar'));

        $this
            ->uploader
            ->expects($this->once())
            ->method('setAllowedExtensions')
            ->with(['ret', 'txt'])
            ->will($this->returnValue($this->uploader));

        $this
            ->uploader
            ->expects($this->once())
            ->method('setAllowCreateFolders')
            ->with(true)
            ->will($this->returnValue($this->uploader));

        $this
            ->uploader
            ->expects($this->once())
            ->method('addValidateCallback')
            ->with('validate', $this->returnsFileReader, 'validateReturnsFile')
            ->will($this->returnValue($this->uploader));

        $this
            ->uploader
            ->expects($this->once())
            ->method('save')
            ->with('/foo/bar')
            ->will($this->returnValue(false));

        $this
            ->uploaderFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->uploader));

        $resultRedirect = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['setPath'])
            ->getMock();

        $resultRedirect
            ->expects($this->once())
            ->method('setPath')
            ->with('boleto/returns/uploadform')
            ->will($this->returnValue($resultRedirect));

        $this
            ->subject
            ->expects($this->once())
            ->method('_getResultRedirect')
            ->will($this->returnValue($resultRedirect));

        $this
            ->messageManager
            ->expects($this->once())
            ->method('addErrorMessage');

        $return = $this->subject->execute();

        $this->assertEquals($resultRedirect, $return);
    }

    public function testExecuteAddsErrorMessageAndRedirectsToFormWhenCantSaveReturnsFileVariation()
    {
        $this
            ->returnsFileReader
            ->expects($this->once())
            ->method('getDestinationPath')
            ->will($this->returnValue('/foo/bar'));

        $this
            ->uploader
            ->expects($this->once())
            ->method('setAllowedExtensions')
            ->with(['ret', 'txt'])
            ->will($this->returnValue($this->uploader));

        $this
            ->uploader
            ->expects($this->once())
            ->method('setAllowCreateFolders')
            ->with(true)
            ->will($this->returnValue($this->uploader));

        $this
            ->uploader
            ->expects($this->once())
            ->method('addValidateCallback')
            ->with('validate', $this->returnsFileReader, 'validateReturnsFile')
            ->will($this->returnValue($this->uploader));

        $this
            ->uploader
            ->expects($this->once())
            ->method('save')
            ->with('/foo/bar')
            ->will($this->returnValue(true));

        $this
            ->uploaderFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->uploader));

        $this
            ->uploader
            ->expects($this->any())
            ->method('getUploadedFileName')
            ->will($this->returnValue('CN93029B.RET'));

        $this
            ->returnsFileReader
            ->expects($this->once())
            ->method('returnsFileExists')
            ->with('CN93029B.RET')
            ->will($this->returnValue(true));

        $resultRedirect = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['setPath'])
            ->getMock();

        $resultRedirect
            ->expects($this->once())
            ->method('setPath')
            ->with('boleto/returns/uploadform')
            ->will($this->returnValue($resultRedirect));

        $this
            ->subject
            ->expects($this->once())
            ->method('_getResultRedirect')
            ->will($this->returnValue($resultRedirect));

        $this
            ->messageManager
            ->expects($this->once())
            ->method('addErrorMessage');

        $return = $this->subject->execute();

        $this->assertEquals($resultRedirect, $return);
    }

    public function testExecuteAddsSuccessMessageAndRedirectsToIndexWhenSuccessfullySavesFile()
    {
        $this
            ->returnsFileReader
            ->expects($this->once())
            ->method('getDestinationPath')
            ->will($this->returnValue('/foo/bar'));

        $this
            ->uploader
            ->expects($this->once())
            ->method('setAllowedExtensions')
            ->with(['ret', 'txt'])
            ->will($this->returnValue($this->uploader));

        $this
            ->uploader
            ->expects($this->once())
            ->method('setAllowCreateFolders')
            ->with(true)
            ->will($this->returnValue($this->uploader));

        $this
            ->uploader
            ->expects($this->once())
            ->method('addValidateCallback')
            ->with('validate', $this->returnsFileReader, 'validateReturnsFile')
            ->will($this->returnValue($this->uploader));

        $this
            ->uploader
            ->expects($this->once())
            ->method('save')
            ->with('/foo/bar')
            ->will($this->returnValue(true));

        $this
            ->uploaderFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->uploader));

        $this
            ->uploader
            ->expects($this->any())
            ->method('getUploadedFileName')
            ->will($this->returnValue('CN93029B.RET'));

        $this
            ->returnsFileReader
            ->expects($this->once())
            ->method('returnsFileExists')
            ->with('CN93029B.RET')
            ->will($this->returnValue(false));

        $this
            ->returnsFileReader
            ->expects($this->once())
            ->method('readAndSaveReturnsFile')
            ->with('/foo/bar/CN93029B.RET');

        $resultRedirect = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['setPath'])
            ->getMock();

        $resultRedirect
            ->expects($this->once())
            ->method('setPath')
            ->with('boleto/returns/index')
            ->will($this->returnValue($resultRedirect));

        $this
            ->subject
            ->expects($this->once())
            ->method('_getResultRedirect')
            ->will($this->returnValue($resultRedirect));

        $this
            ->messageManager
            ->expects($this->once())
            ->method('addSuccessMessage');

        $return = $this->subject->execute();

        $this->assertEquals($resultRedirect, $return);
    }


}