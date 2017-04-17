<?php

namespace Gabrielqs\Boleto\Api\Data;

interface ReturnsFileEventInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const RETURNS_FILE_EVENT_ID    = 'returns_file_event_id';
    const RETURNS_FILE_ID          = 'returns_file_id';
    const DESCRIPTION              = 'description';
    const CREATION_TIME            = 'creation_time';
    /**#@-*/

    /**
     * Get ID
     * @return int|null
     */
    public function getId();

    /**
     * Get Returns File ID
     * @return int|null
     */
    public function getReturnsFileId();

    /**
     * Get Description
     * @return string|null
     */
    public function getDescription();

    /**
     * Get Creation Time
     * @return string|null
     */
    public function getCreationTime();

    /**
     * Set ID
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Set Returns File ID
     * @param int $id
     * @return $this
     */
    public function setReturnsFileId($id);

    /**
     * Set Description
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * Set Creation Time
     *
     * @param string $creationTime
     * @return $this
     */
    public function setCreationTime($creationTime);
}
