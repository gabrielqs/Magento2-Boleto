<?php

namespace Gabrielqs\Boleto\Api\Data;

interface ReturnsFileOrderInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const RETURNS_FILE_ORDER_ID    = 'returns_file_order_id';
    const RETURNS_FILE_ID          = 'returns_file_id';
    const ORDER_ID                 = 'order_id';
    /**#@-*/

    /**
     * Get ID
     * @return int|null
     */
    public function getId();

    /**
     * Get Returns File Id
     * @return integer|null
     */
    public function getReturnsFileId();

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
     * Set Returns File Id
     * @param integer $returnsFileId
     * @return $this
     */
    public function setReturnsFileId($returnsFileId);

    /**
     * Set OrderId
     * @param integer $orderId
     * @return $this
     */
    public function setOrderId($orderId);
}
