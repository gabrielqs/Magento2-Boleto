<?php

namespace Gabrielqs\Boleto\Model\Remittance\File;

use \Magento\Framework\Model\AbstractModel;
use \Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\Model\Context;
use \Magento\Framework\Registry;
use \Magento\Framework\Model\ResourceModel\AbstractResource;
use \Magento\Framework\Data\Collection\AbstractDb;
use \Magento\Sales\Model\Order as SalesOrder;
use \Magento\Sales\Model\OrderRepository as SalesOrderRepository;
use \Gabrielqs\Boleto\Api\Data\RemittanceFileOrderInterface;

/**
 * Class Order
 * @package Gabrielqs\Boleto\Model\Remittance\File
 */
class Order extends AbstractModel implements RemittanceFileOrderInterface, IdentityInterface
{
    /**
     * Sales Order Model
     * @var SalesOrder|null
     */
    protected $_order = null;

    /**
     * Sales Order Factory
     * @var SalesOrderRepository|null
     */
    protected $_orderRepository = null;

    /**
     * Cache tag
     */
    const CACHE_TAG = 'boleto_remittance_file_order';

    /**
     * Remittance File constructor.
     * @param Context $context
     * @param Registry $registry
     * @param SalesOrderRepository $orderRepository
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        SalesOrderRepository $orderRepository,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $return = parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->_orderRepository = $orderRepository;

        return $return;
    }

    /**
     * Remittance File Order Constructor
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Gabrielqs\Boleto\Model\ResourceModel\Remittance\File\Order');
    }

    /**
     * Get ID
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::REMITTANCE_FILE_ORDER_ID);
    }

    /**
     * Return identities
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Returns the Sales Order object related to this entity
     * @return SalesOrder
     */
    public function getOrder()
    {
        if ($this->_order === null) {
            $order = $this->_orderRepository->get($this->getOrderId());
            $this->_order = $order;
        }
        return $this->_order;
    }

    /**
     * Get Order Id
     * @return integer|null
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * Get Remittance File Id
     * @return integer|null
     */
    public function getRemittanceFileId()
    {
        return $this->getData(self::REMITTANCE_FILE_ID);
    }


    /**
     * Set ID
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::REMITTANCE_FILE_ORDER_ID, $id);
    }

    /**
     * Set OrderId
     * @param integer $orderId
     * @return $this
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Set Remittance File Id
     * @param integer $remittanceFileId
     * @return $this
     */
    public function setRemittanceFileId($remittanceFileId)
    {
        return $this->setData(self::REMITTANCE_FILE_ID, $remittanceFileId);
    }
}
