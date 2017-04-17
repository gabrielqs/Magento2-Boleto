<?php

namespace Gabrielqs\Boleto\Helper\Returns;

use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\App\Helper\Context;
use \Magento\Framework\Exception\LocalizedException;
use \Magento\Sales\Model\Order;
use \Magento\Sales\Model\OrderRepository;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Cnab\Factory as CnabFactory;
use \Cnab\Retorno\IArquivo as ReturnsFileReaderModel;
use \Cnab\Retorno\IDetalhe as ReturnsFileDetailModel;
use \Gabrielqs\Boleto\Model\Returns\File as ReturnsFile;
use \Gabrielqs\Boleto\Model\Returns\FileFactory as ReturnsFileFactory;
use \Gabrielqs\Boleto\Model\Returns\FileRepository as ReturnsFileRepository;
use \Gabrielqs\Boleto\Helper\Boleto\Data as BoletoHelper;

class Reader extends AbstractHelper
{
    /**
     * Boleto Helper
     * @var BoletoHelper
     */
    protected $boletoHelper = null;

    /**
     * Cnab Factory
     * @var CnabFactory $cnabFactory
     */
    protected $cnabFactory = null;

    /**
     * Order Repository
     * @var OrderRepository $orderRepository
     */
    protected $orderRepository = null;

    /**
     * Returns File Factory
     * @var ReturnsFileFactory
     */
    protected $returnsFileFactory;

    /**
     * Returns File Repository
     * @var ReturnsFileRepository $returnsFileRepository
     */
    protected $returnsFileRepository = null;

    /**
     * Returns Files Array
     * @var ReturnsFileReaderModel[] $returnsFiles
     */
    protected $returnsFiles = [];

    /**
     * Search Criteria Builder
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder = null;

    /**
     * Reader constructor.
     * @param Context $context
     * @param CnabFactory $cnabFactory
     * @param OrderRepository $orderRepository
     * @param ReturnsFileRepository $returnsFileRepository
     * @param ReturnsFileFactory $returnsFileFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param BoletoHelper $boletoHelper
     */
    public function __construct(
        Context $context,
        CnabFactory $cnabFactory,
        OrderRepository $orderRepository,
        ReturnsFileRepository $returnsFileRepository,
        ReturnsFileFactory $returnsFileFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        BoletoHelper $boletoHelper
    ) {
        parent::__construct($context);
        $this->cnabFactory = $cnabFactory;
        $this->orderRepository = $orderRepository;
        $this->returnsFileRepository = $returnsFileRepository;
        $this->returnsFileFactory = $returnsFileFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->boletoHelper = $boletoHelper;
    }

    /**
     * Checks if file already exists in database
     * @param string $filePath
     * @return bool
     */
    public function returnsFileExists($filePath)
    {
        $return = false;
        # Checks if a file with the same name already exists
        $fileName = basename($filePath);
        $searchCriteria = $this
            ->searchCriteriaBuilder
            ->addFilter(ReturnsFile::NAME, $fileName, 'eq')
            ->create();
        $returnsFileList = $this->returnsFileRepository->getList($searchCriteria);
        if ($returnsFileList->getTotalCount() > 0) {
            $return = true;
        }
        return $return;
    }

    /**
     * Returns Return files destination path
     * @return string
     */
    public function getDestinationPath()
    {
        $returnsFile = $this->returnsFileFactory->create();
        $destinationPath = $returnsFile->getStoragePath();
        return $destinationPath;
    }

    /**
     * Retrieves Detail Objects from Return Files
     * @param string $path
     * @return ReturnsFileDetailModel[]
     */
    protected function _getDetailsFromPath($path)
    {
        $returnsFileObject = $this->_getReturnsFileObjectFromPath($path);
        return (array) $returnsFileObject->listDetalhes();
    }

    /**
     * Loads a return file, parses it using CnabPHP and returns only the OrderIds and paid values
     * @param ReturnsFile $file
     * @return \stdClass[]
     */
    public function getOrdersIdsAndValues(ReturnsFile $file)
    {
        $return = [];
        $path = $file->getPath();
        $details = $this->_getDetailsFromPath($path);
        foreach ($details as $detail) {
            $returnDetail = new \stdClass();
            $returnDetail->orderId = $this->boletoHelper->convertBoletoIdToOrderIncrementId($detail->getNossoNumero());
            $returnDetail->value = (float) $detail->getValorRecebido();
            $return[] = $returnDetail;
        }
        return $return;
    }

    /**
     * Reads and returns a returns file
     * @param string $path
     * @return ReturnsFileReaderModel
     */
    protected function _getReturnsFileObjectFromPath($path)
    {
        if (!array_key_exists($path, $this->returnsFiles)) {
            $this->returnsFiles[$path] = $this->cnabFactory->createRetorno($path);
        }
        return $this->returnsFiles[$path];
    }

    /**
     * Reads file content from path
     * @param string $path
     * @return string[]
     */
    protected function _getReturnsFileOrderIdsFromPath($path)
    {
        $return = [];
        $file = $this->_getReturnsFileObjectFromPath($path);
        foreach ($file->listDetalhes() as $detail) {
            $boletoId = $detail->getNossoNumero();
            $return[] = $this->boletoHelper->convertBoletoIdToOrderIncrementId($boletoId);
        }
        return $return;
    }

    /**
     * Reads the given returns file and creates the corresponding returns file entities
     * @param string $filePath
     * @return void
     * @throws LocalizedException
     */
    public function readAndSaveReturnsFile($filePath)
    {
        # Saving Returns File
        $fileName = basename($filePath);
        /** @var ReturnsFile $returnsFile */
        $returnsFile = $this->returnsFileFactory->create();
        $returnsFile
            ->setName($fileName)
            ->setStatus(ReturnsFile::STATUS_NEW);
        $this->returnsFileRepository->save($returnsFile);

        # Creating Returns File Orders
        $orderIds = $this->_getReturnsFileOrderIdsFromPath($filePath);
        foreach ($orderIds as $orderId) {
            $returnsFile->createNewOrderByIncrementId($orderId);
        }

        # Creating Returns File Event
        $returnsFile->createNewEvent('File Imported');
    }

    /**
     * File Validation
     * @param string $filePath
     * @return boolean
     * @throws \Exception
     */
    public function validateReturnsFile($filePath)
    {
        # Checks if, inside the file, an existing order is found
        $orderIds = $this->_getReturnsFileOrderIdsFromPath($filePath);
        $searchCriteria = $this
            ->searchCriteriaBuilder
            ->addFilter(Order::INCREMENT_ID, $orderIds, 'in')
            ->create();
        $orderList = $this->orderRepository->getList($searchCriteria);
        if ($orderList->getTotalCount() == 0) {
            throw new LocalizedException(__('None of the file rows correspond to an order.'));
        }

        # No errors found
        return true;
    }
}