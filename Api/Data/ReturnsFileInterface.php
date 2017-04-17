<?php

namespace Gabrielqs\Boleto\Api\Data;

interface ReturnsFileInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const RETURNS_FILE_ID         = 'returns_file_id';
    const NAME                    = 'name';
    const STATUS                  = 'status';
    const CREATION_TIME           = 'creation_time';
    const UPDATE_TIME             = 'update_time';
    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get Name
     *
     * @return string|null
     */
    public function getName();

    /**
     * Get Status
     *
     * @return integer|null
     */
    public function getStatus();

    /**
     * Get Creation Time
     *
     * @return string|null
     */
    public function getCreationTime();

    /**
     * Get Update Time
     *
     * @return string|null
     */
    public function getUpdateTime();


    /**
     * Set ID
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Set Name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Set Status
     *
     * @param int $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Set Creation Time
     *
     * @param string $creationTime
     * @return $this
     */
    public function setCreationTime($creationTime);

    /**
     * Set Update Time
     *
     * @param string $updateTime
     * @return $this
     */
    public function setUpdateTime($updateTime);
}
