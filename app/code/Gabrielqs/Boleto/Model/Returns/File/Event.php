<?php

namespace Gabrielqs\Boleto\Model\Returns\File;

use \Magento\Framework\Model\AbstractModel;
use \Magento\Framework\DataObject\IdentityInterface;
use \Gabrielqs\Boleto\Api\Data\ReturnsFileEventInterface;

/**
 * Class Event
 * @package Gabrielqs\Boleto\Model\Returns\File
 */
class Event extends AbstractModel implements ReturnsFileEventInterface, IdentityInterface
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'boleto_returns_file_event';

    /**
     * Returns File Event Constructor
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Gabrielqs\Boleto\Model\ResourceModel\Returns\File\Event');
    }

    /**
     * Get Creation Time
     * @return string|null
     */
    public function getCreationTime()
    {
        return $this->getData(self::CREATION_TIME);
    }

    /**
     * Get Description
     * @return string|null
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * Get ID
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::RETURNS_FILE_EVENT_ID);
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
     * Get Returns File ID
     * @return int|null
     */
    public function getReturnsFileId()
    {
        return $this->getData(self::RETURNS_FILE_ID);
    }

    /**
     * Set Creation Time
     *
     * @param string $creationTime
     * @return $this
     */
    public function setCreationTime($creationTime)
    {
        return $this->setData(self::CREATION_TIME, $creationTime);
    }

    /**
     * Set Description
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * Set ID
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::RETURNS_FILE_EVENT_ID, $id);
    }

    /**
     * Set Returns File ID
     * @param int $id
     * @return $this
     */
    public function setReturnsFileId($id)
    {
        return $this->setData(self::RETURNS_FILE_ID, $id);
    }
}
