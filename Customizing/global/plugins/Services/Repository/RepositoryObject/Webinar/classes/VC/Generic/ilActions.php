<?php
declare(strict_types=1);

namespace CaT\Plugins\Webinar\VC\Generic;

use CaT\Plugins\Webinar\VC;
use CaT\Plugins\Webinar\Config\Config;

/**
 * Actions for Generic VC
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilActions implements VC\VCActions
{
    /**
     * @var \ilObjWebinar
     */
    protected $object;

    /**
     * @var ilDB
     */
    protected $generic_db;

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

    public function __construct(\ilObjWebinar $object, ilDB $generic_db, Config $config)
    {
        $this->object = $object;
        $this->generic_db = $generic_db;
        $this->config = $config;
        $this->import = null;
        $this->export = null;
    }

    /**
     * Create a new Generic VC settings entry
     *
     * @param string | null	$password
     * @param string | null	$tutor_login
     * @param string | null	$tutor_password
     *
     * @return Settings
     */
    public function create(
        ?string $password = null,
        ?string $tutor_login = null,
        ?string $tutor_password = null,
        ?int $minutes_required = null
    ) : Settings {
        return $this->generic_db->create($this->getObjectId(), $password, $tutor_login, $tutor_password, $minutes_required);
    }

    /**
     * Update an existing Generic VC settings entry
     *
     * @param Settings 	$settings
     *
     * @return void
     */
    public function update(Settings $settings) : void
    {
        $this->generic_db->update($settings);
    }

    /**
     * Get Generic VC Settings
     *
     * @return Settings
     */
    public function select() : Settings
    {
        return $this->generic_db->select($this->getObjectId());
    }

    /**
     * Delete Generic VC settings entry
     *
     * @return void
     */
    public function delete() : void
    {
        $this->generic_db->delete($this->getObjectId());
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
        $this->generic_db->createUnknownParticipant($this->getObjectId(), $user_name, $email, $phone, $company, $minutes, $user_id);
    }

    /**
     * Delete unknown participants
     *
     * @return void
     */
    public function deleteUnknownParticipants() : void
    {
        $this->generic_db->deleteUnknownParticipants($this->getObjectId());
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
        $this->generic_db->deleteUnknownParticipant($id);
    }

    /**
     * Set minutes of booked users to null
     *
     * @return void
     */
    public function resetMinutesOfBookedUsers() : void
    {
        $this->generic_db->resetMinutesOfBookedUsers($this->getObjectId());
    }

    /**
     * Get all unknown participants for current object
     *
     * @return Participant[]
     */
    public function getUnknownParticipants() : array
    {
        return $this->generic_db->getUnknownParticipants($this->getObjectId());
    }

    /**
     * @inheritdoc
     */
    public function updateParticipant(VC\Participant $participant) : void
    {
        $this->generic_db->updateParticipant($participant);
    }

    /**
     * @inheritdoc
     */
    public function getParticipantByUserName(string $user_name) : ?VC\Participant
    {
        return $this->generic_db->getParticipantByUserName($this->getObjectId(), $user_name, $this->config->getPhoneType());
    }

    /**
     * Get current object
     *
     * @return \ilObjWebinar
     */
    public function getObject() : \ilObjWebinar
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
        $unknown = $this->generic_db->getUnknownParticipants($this->getObjectId());

        return array_merge($booked, $unknown);
    }

    /**
     * Get booked participants
     *
     * @return Participant[]
     */
    public function getBookedParticipants() : array
    {
        return $this->generic_db->getBookedParticipants($this->getObjectId(), $this->config->getPhoneType());
    }

    /**
     * Get the id of current object
     *
     * @return int
     */
    protected function getObjectId() : int
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
        return $this->generic_db->getUnknownParticipantByLogin($this->getObjectId(), $user_name);
    }
}
