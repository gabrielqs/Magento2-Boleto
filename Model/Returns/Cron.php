<?php

namespace Gabrielqs\Boleto\Model\Returns;

use \Magento\Sales\Model\Order;
use \Magento\Sales\Model\Order\Invoice;
use \Magento\Sales\Model\OrderRepository;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Magento\Sales\Model\Service\InvoiceService;
use \Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use \Gabrielqs\Boleto\Helper\Returns\Reader as ReturnsFileReader;
use \Gabrielqs\Boleto\Model\Returns\FileRepository as ReturnsFileRepository;
use \Gabrielqs\Boleto\Model\Returns\File as ReturnsFile;
use \Gabrielqs\Boleto\Api\Data\ReturnsFileSearchResultsInterface;

class Cron
{

    /**
     * Invoice Sender
     * @var InvoiceSender
     */
    protected $_invoiceSender = null;

    /**
     * Invoice Service
     * @var InvoiceService
     */
    protected $_invoiceService = null;

    /**
     * Order Repository
     * @var OrderRepository|null
     */
    protected $_orderRepository = null;

    /**
     * Returns File Reader
     * @var ReturnsFileReader|null
     */
    protected $_returnsFileReader = null;

    /**
     * Returns File Repository
     * @var ReturnsFileRepository|null
     */
    protected $_returnsFileRepository = null;

    /**
     * Search Criteria Builder
     * @var SearchCriteriaBuilder|null
     */
    protected $_searchCriteriaBuilder = null;

    /**
     * Cron constructor
     * @param OrderRepository $orderRepository
     * @param ReturnsFileReader $returnsFileReader
     * @param ReturnsFileRepository $returnsFileRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param InvoiceService $invoiceService
     * @param InvoiceSender $invoiceSender
     */
    public function __construct(
        OrderRepository $orderRepository,
        ReturnsFileReader $returnsFileReader,
        ReturnsFileRepository $returnsFileRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        InvoiceService $invoiceService,
        InvoiceSender $invoiceSender
    ) {
        $this->_orderRepository = $orderRepository;
        $this->_returnsFileReader = $returnsFileReader;
        $this->_returnsFileRepository = $returnsFileRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_invoiceService = $invoiceService;
        $this->_invoiceSender = $invoiceSender;
    }

    /**
     * Loops through the return files with status = 1 (new) and processes the orders attached to them,
     * creating invoices when applicable.
     * Files statuses are changed after processing is finished, reflecting the processing result. Status might
     * either become 2 (error) or 3 (success)
     * One event is created for each order processed, so in case of an error it will be clear for the user what
     * happened
     * @return void
     */
    public function processFiles()
    {
        $files = $this->_getFiles();
        /* @var ReturnsFile $file */
        foreach ($files as $file) {
            $success = true;
            $fileOrdersAndValues = $this->_returnsFileReader->getOrdersIdsAndValues($file);
            foreach ($fileOrdersAndValues as $orderAndValueInfo) {
                $orderId = $orderAndValueInfo->orderId;
                $value = $orderAndValueInfo->value;
                $order = $this->_getOrderFromIncrementId($orderId);
                if ($order) {
                    if ($order->getGrandTotal() <= $value) {
                        if ($order->canInvoice()) {
                            $this->_createInvoice($order);
                            $file->createNewEvent(__('Successfully invoiced order - %1', $orderId));
                        } else {
                            $file->createNewEvent(__('The order has been paid, but we its state doesn\'t allow ' .
                                'the invoice to be created - %1', $orderId));
                            $success = false;
                        }
                    } else {
                        $file->createNewEvent(__('Paid value is less than order grand total - %1', $orderId));
                        $success = false;
                    }
                } else {
                    $file->createNewEvent(__('Order not found - %1', $orderId));
                    $success = false;
                }
            }

            $this->_updateReturnsFileStatus($file, $success);
        }
    }

    /**
     * Creates invoice for the given order
     * @param Order $order
     * @return Invoice
     */
    protected function _createInvoice(Order $order)
    {
        /* @var Invoice $invoice */
        $invoice = $this->_invoiceService->prepareInvoice($order);

        $invoice
            ->register()
            ->capture()
            ->save();

        $this->_invoiceSender->send($invoice);

        $message = __('Notified customer about invoice #%1.', $invoice->getId());
        $order
            ->addStatusHistoryComment($message)
            ->setIsCustomerNotified(true)
            ->save();

        return $invoice;
    }

    /**
     * Gets the list of files to be processed
     * @return ReturnsFileSearchResultsInterface
     */
    protected function _getFiles()
    {
        $searchCriteria = $this
            ->_searchCriteriaBuilder
            ->addFilter(ReturnsFile::STATUS, ReturnsFile::STATUS_NEW, 'eq')
            ->create();
        return $this->_returnsFileRepository->getList($searchCriteria);
    }

    /**
     * Gets an order by it's increment id
     * @param string $incrementId
     * @return Order
     */
    protected function _getOrderFromIncrementId($incrementId)
    {
        $return = null;
        $searchCriteriaOrder = $this
            ->_searchCriteriaBuilder
            ->addFilter(Order::INCREMENT_ID, $incrementId, 'eq')
            ->create();
        $orderList = $this->_orderRepository->getList($searchCriteriaOrder);
        if ($orderList->getTotalCount() >= 1) {
            $return = $orderList->getFirstItem();
        }
        return $return;
    }

    /**
     * Updates the return file status according to the processing status
     * @param ReturnsFile $file
     * @param bool $success
     * @return void
     */
    protected function _updateReturnsFileStatus(ReturnsFile $file, $success)
    {
        if ($success) {
            $file->setStatus(ReturnsFile::STATUS_SUCCESS);
        } else {
            $file->setStatus(ReturnsFile::STATUS_ERROR);
        }
        $this->_returnsFileRepository->save($file);
    }

}