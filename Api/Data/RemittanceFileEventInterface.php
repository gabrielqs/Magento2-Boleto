<?php

namespace Gabrielqs\Boleto\Api\Data;

interface RemittanceFileEventInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const REMITTANCE_FILE_EVENT_ID = 'remittance_file_event_id';
    const REMITTANCE_FILE_ID       = 'remittance_file_id';
    const DESCRIPTION              = 'description';
    const CREATION_TIME            = 'creation_time';
    /**#@-*/

    /**
     * Get ID
     * @return int|null
     */
    public function getId();

    /**
     * Get Remittance File ID
     * @return int|null
     */
    public function getRemittanceFileId();

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
     * Set Remittance File ID
     * @param int $id
     * @return $this
     */
    public function setRemittanceFileId($id);

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
