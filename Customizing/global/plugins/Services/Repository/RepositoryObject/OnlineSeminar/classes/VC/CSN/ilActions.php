<?php
declare(strict_types=1);

namespace CaT\Plugins\OnlineSeminar\VC\CSN;

use CaT\Plugins\OnlineSeminar\VC;
use CaT\Plugins\OnlineSeminar\Config\Config;

/**
 * Actions for CSN VC
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilActions implements VC\VCActions
{
    /**
     * @var \ilObjOnlineSeminar
     */
    protected $object;

    /**
     * @var ilDB
     */
    protected $csn_db;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Import
     */
    protected $import;

    /**
     * @var Export
     */
    protected $export;

    public function __construct(\ilObjOnlineSeminar $object, ilDB $csn_db, Config $config)
    {
        $this->object = $object;
        $this->csn_db = $csn_db;
        $this->config = $config;
        $this->import = null;
        $this->export = null;
    }

    /**
     * Create a new CSN VC settings entry
     *
     * @param string 	$phone
     * @param string 	$pin
     * @param int 	$minutes_required
     *
     * @return Settings
     */
    public function create(?string $phone = null, ?string $pin = null, ?int $minutes_required = null) : Settings
    {
        return $this->csn_db->create($this->getObjectId(), $phone, $pin, $minutes_required);
    }

    /**
     * Update an existing CSN VC settings entry
     *
     * @param Settings 	$settings
     *
     * @return void
     */
    public function update(Settings $settings) : void
    {
        $this->csn_db->update($settings);
    }

    /**
     * Get CSN VC Settings
     *
     * @return Settings
     */
    public function select() : Settings
    {
        return $this->csn_db->select($this->getObjectId());
    }

    /**
     * Delete CSN VC settings entry
     *
     * @return void
     */
    public function delete() : void
    {
        $this->csn_db->delete($this->getObjectId());
    }

    /**
     * @inheritdoc
     */
    public function getMinutesRequired() : int
    {
        return $this->select()->getMinutesRequired();
    }

    /**
     * @inheritdoc
     */
    public function createUnknownParticipant(
        string $user_name,
        string $email,
        string $phone,
        string $company,
        int $minutes,
        ?int $user_id = null
    ) : VC\Participant {
        $this->csn_db->createUnknownParticipant($this->getObjectId(), $user_name, $email, $phone, $company, $minutes, $user_id);
    }

    /**
     * Delete unknown participants
     *
     * @return void
     */
    public function deleteUnknownParticipants() : void
    {
        $this->csn_db->deleteUnknownParticipants($this->getObjectId());
    }

    /**
     * Delete unknown participant
     *
     * @param int 	$id
     *
     * @return void
     */
    public function deleteUnknownParticipant(int $id) : void
    {
        $this->csn_db->deleteUnknownParticipant($id);
    }

    /**
     * Set minutes of booked users to null
     *
     * @return void
     */
    public function resetMinutesOfBookedUsers() : void
    {
        $this->csn_db->resetMinutesOfBookedUsers($this->getObjectId());
    }

    /**
     * Get all unknown participants for current object
     *
     * @return Participant[]
     */
    public function getUnknownParticipants() : array
    {
        return $this->csn_db->getUnknownParticipants($this->getObjectId());
    }

    /**
     * @inheritdoc
     */
    public function updateParticipant(VC\Participant $participant) : void
    {
        $this->csn_db->updateParticipant($participant);
    }

    /**
     * @inheritdoc
     */
    public function getParticipantByUserName(string $user_name) : ?VC\Participant
    {
        return $this->csn_db->getParticipantByUserName($this->getObjectId(), $user_name, $this->config->getPhoneType());
    }

    /**
     * Get current object
     *
     * @return \ilObjOnlineSeminar
     */
    public function getObject() : \ilObjOnlineSeminar
    {
        if ($this->object === null) {
            throw new \Exception(__METHOD__ . " no object is set.");
        }

        return $this->object;
    }

    /**
     * Get booked and unknown participants
     *
     * @return Participant[]
     */
    public function getAllParticipants() : array
    {
        $booked = $this->getBookedParticipants();
        $unknown = $this->csn_db->getUnknownParticipants($this->getObjectId());

        return array_merge($booked, $unknown);
    }

    /**
     * Get booked participants
     *
     * @return Participant[]
     */
    public function getBookedParticipants() : array
    {
        return $this->csn_db->getBookedParticipants($this->getObjectId(), $this->config->getPhoneType());
    }

    /**
     * Get the id of current object
     *
     * @return int
     */
    protected function getObjectId()
    {
        if ($this->object === null) {
            throw new \Exception(__METHOD__ . " no object is set.");
        }

        return (int) $this->object->getId();
    }

    /**
     * @inheritdoc
     */
    public function getUnknownParticipantByLogin(string $user_name) : ?VC\Participant
    {
        return $this->csn_db->getUnknownParticipantByLogin($this->getObjectId(), $user_name);
    }
}
