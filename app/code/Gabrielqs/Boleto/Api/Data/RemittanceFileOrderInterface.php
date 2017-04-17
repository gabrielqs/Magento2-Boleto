<?php

namespace Gabrielqs\Boleto\Api\Data;

interface RemittanceFileOrderInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const REMITTANCE_FILE_ORDER_ID = 'remittance_file_order_id';
    const REMITTANCE_FILE_ID       = 'remittance_file_id';
    const ORDER_ID                 = 'order_id';
    /**#@-*/

    /**
     * Get ID
     * @return int|null
     */
    public function getId();

    /**
     * Get Remittance File Id
     * @return integer|null
     */
    public function getRemittanceFileId();

    /**
     * Get Order Id
     * @return integer|null
     */
    public function getOrderId();


    /**
     * Set ID
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Set Remittance File Id
     * @param integer $remittanceFileId
     * @return $this
     */
    public function setRemittanceFileId($remittanceFileId);

    /**
     * Set OrderId
     * @param integer $orderId
     * @return $this
     */
    public function setOrderId($orderId);
}
