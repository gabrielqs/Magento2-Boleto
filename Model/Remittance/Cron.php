<?php

namespace Gabrielqs\Boleto\Model\Remittance;

use \Magento\Sales\Model\Order as SalesOrder;
use \Magento\Sales\Model\ResourceModel\Order\Collection as SalesOrderCollection;
use \Magento\Sales\Model\ResourceModel\Order\CollectionFactory as SalesOrderCollectionFactory;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Magento\Framework\Stdlib\DateTime\DateTime;
use \Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use \Gabrielqs\Boleto\Helper\Boleto\Data as BoletoHelper;
use \Gabrielqs\Boleto\Helper\Remittance\Generator as RemittanceFileGenerator;
use \Gabrielqs\Boleto\Model\Remittance\FileRepository as RemittanceFileRepository;
use \Gabrielqs\Boleto\Model\Remittance\FileFactory as RemittanceFileFactory;
use \Gabrielqs\Boleto\Model\Remittance\File as RemittanceFile;

class Cron
{

    /**
     * Boleto Helper
     * @var BoletoHelper $_boletoHelper
     */
    protected $_boletoHelper = null;

    /**
     * Date Time Factory
     * @var DateTimeFactory $_dateFactory
     */
    protected $_dateFactory = null;

    /**
     * Search Criteria Builder
     * @var SearchCriteriaBuilder|null
     */
    protected $_searchCriteriaBuilder = null;

    /**
     * Order collection factory
     * @var SalesOrderCollectionFactory|null
     */
    protected $_salesOrderCollectionFactory = null;

    /**
     * Remittance File Factory
     * @var RemittanceFileFactory|null
     */
    protected $_remittanceFileFactory = null;

    /**
     * Remittance File Generator
     * @var RemittanceFileGenerator|null
     */
    protected $_remittanceFileGenerator = null;

    /**
     * Remittance File Repository
     * @var RemittanceFileRepository|null
     */
    protected $_remittanceFileRepository = null;

    /**
     * Cron constructor
     * @param SalesOrderCollectionFactory $salesOrderCollectionFactory
     * @param RemittanceFileGenerator $remittanceFileGenerator
     * @param RemittanceFileRepository $remittanceFileRepository
     * @param RemittanceFileFactory $remittanceFileFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param DateTimeFactory $dateFactory
     * @param BoletoHelper $boletoHelper
     */
    public function __construct(
        SalesOrderCollectionFactory $salesOrderCollectionFactory,
        RemittanceFileGenerator $remittanceFileGenerator,
        RemittanceFileRepository $remittanceFileRepository,
        RemittanceFileFactory $remittanceFileFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DateTimeFactory $dateFactory,
        BoletoHelper $boletoHelper
    ) {
        $this->_salesOrderCollectionFactory = $salesOrderCollectionFactory;
        $this->_remittanceFileGenerator = $remittanceFileGenerator;
        $this->_remittanceFileRepository = $remittanceFileRepository;
        $this->_remittanceFileFactory = $remittanceFileFactory;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_dateFactory = $dateFactory;
        $this->_boletoHelper = $boletoHelper;
    }


    /**
     * Retrieves all orders created with boleto as a payment method and creates a remittance file, saving it to database
     * Updates the file status accordingly after processing is finished, reflecting the processing result. Status might
     * either become 2 (error) or 3 (success)
     * @return void
     */
    public function processOrders()
    {
        $orders = $this->_getOrders();
        if ($orders->count()) {
            $remittanceFile = $this->_remittanceFileFactory->create();
            $fileName = $this->_remittanceFileGenerator->generateFileName();
            $remittanceFile
                ->setName($fileName)
                ->setStatus(RemittanceFile::STATUS_NEW);
            $this->_remittanceFileRepository->save($remittanceFile);
            try {
                $content = $this->_remittanceFileGenerator->generateFileContent($orders);
                $remittanceFile
                    ->saveFileToFileSystem($content);
                foreach ($orders->getItems() as $order) {
                    $remittanceFile->createNewOrderById($order->getId());
                }
                $remittanceFile->createNewEvent('Remittance file created.');
                $this->_updateRemittanceFileStatus($remittanceFile, true);
            } catch (\Exception $e) {
                $remittanceFile->createNewEvent(__('An error happened while creating the remittance file.'));
                $remittanceFile->createNewEvent($e->getMessage());
                $this->_updateRemittanceFileStatus($remittanceFile, false);
            }
        }
    }

    /**
     * Gets the list of files to be processed
     * @return SalesOrderCollection
     */
    protected function _getOrders()
    {
        /** @var DateTime $date */
        $date = $this->_dateFactory->create();
        $collection = $this->_salesOrderCollectionFactory->create();
        $collection
            ->join(
                ['payment' => 'sales_order_payment'],
                'main_table.entity_id=payment.parent_id',
                ['payment_method' => 'payment.method']
            )
            ->addFieldToFilter('payment.method', ['eq' => $this->_boletoHelper->getMethodCode()])
            ->addFieldToFilter(SalesOrder::CREATED_AT, [
                'from' => $date->date('Y-m-d h:i:s', '-1 days'),
                'to' => $date->date('Y-m-d h:i:s'),
            ]);
        return $collection;
    }

    /**
     * Updates the return file status according to the processing status
     * @param RemittanceFile $file
     * @param bool $success
     * @return void
     */
    protected function _updateRemittanceFileStatus(RemittanceFile $file, $success)
    {
        if ($success) {
            $file->setStatus(RemittanceFile::STATUS_SUCCESS);
        } else {
            $file->setStatus(RemittanceFile::STATUS_ERROR);
        }
        $this->_remittanceFileRepository->save($file);
    }

}