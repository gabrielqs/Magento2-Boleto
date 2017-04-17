<?php

namespace Gabrielqs\Boleto\Controller\Adminhtml\Remittance;

use \Magento\Framework\DataObject;
use \Magento\Backend\Model\View\Result\Page;
use \Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\Registry;
use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Magento\Backend\App\Action\Context;
use \Magento\Framework\Message\Manager as MessageManager;
use \Gabrielqs\Boleto\Model\Remittance\FileRepository as RemittanceFileRepository;
use \Gabrielqs\Boleto\Model\Remittance\File;
use \Gabrielqs\Boleto\Controller\Adminhtml\Remittance\View as Subject;
use \Gabrielqs\Boleto\Controller\Adminhtml\RegistryConstants;

/**
 * Unit Testcase
 */
class ViewTest extends \PHPUnit_Framework_TestCase
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
     * @var Registry
     */
    protected $coreRegistry = null;

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
     * @var PageFactory;
     */
    protected $resultPageFactory = null;

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
            ->setMethods(['_getRemittanceFileIdFromRequest', '_getResultRedirect', 'initPage', '_addTitle'])
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
        $arguments['context'] = $this->context;

        $this->coreRegistry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->setMethods(['register'])
            ->getMock();
        $arguments['coreRegistry'] = $this->coreRegistry;

        $this->resultPageFactory = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $arguments['resultPageFactory'] = $this->resultPageFactory;

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


    public function testExecuteLoadsFileWithRepositoryRemittanceDownloadWithRightContentsAddsIdToRegistry()
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

        $this
            ->remittanceFileRepository
            ->expects($this->once())
            ->method('getById')
            ->with(1039)
            ->will($this->returnValue($remittanceFile));

        $this
            ->coreRegistry
            ->expects($this->once())
            ->method('register')
            ->with(RegistryConstants::CURRENT_REMITTANCE_FILE_ID, 1039);


        $title = $this->getMockBuilder(DataObject::class)
            ->setMethods(['prepend'])
            ->disableOriginalConstructor()
            ->getMock();

        $title
            ->expects($this->any())
            ->method('prepend')
            ->will($this->returnValue($title));


        $config = $this->getMockBuilder(DataObject::class)
            ->setMethods(['getTitle'])
            ->disableOriginalConstructor()
            ->getMock();

        $config
            ->expects($this->any())
            ->method('getTitle')
            ->will($this->returnValue($title));


        $resultPage = $this->getMockBuilder(Page::class)
            ->setMethods(['setActiveMenu', 'addBreadcrumb', 'getConfig'])
            ->disableOriginalConstructor()
            ->getMock();

        $resultPage
            ->expects($this->any())
            ->method('setActiveMenu')
            ->will($this->returnValue($resultPage));

        $resultPage
            ->expects($this->any())
            ->method('addBreadcrumb')
            ->will($this->returnValue($resultPage));

        $resultPage
            ->expects($this->any())
            ->method('getConfig')
            ->will($this->returnValue($config));

        $this->resultPageFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($resultPage));

        $return = $this->subject->execute();

        $this->assertEquals($resultPage, $return);
    }

}