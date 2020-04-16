<?php

namespace CaT\Plugins\Webinar\VC\Generic;

use CaT\Plugins\Webinar\VC;
use CaT\Libs\ExcelWrapper\Spout;
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
    public function create($password = null, $tutor_login = null, $tutor_password = null, $minutes_required = null)
    {
        assert('is_string($password) || is_null($password)');
        assert('is_int($tutor_login) || is_null($tutor_login)');
        assert('is_int($tutor_password) || is_null($tutor_password)');
        assert('is_int($minutes_required) || is_null($minutes_required)');

        return $this->generic_db->create($this->getObjectId(), $password, $tutor_login, $tutor_password, $minutes_required);
    }

    /**
     * Update an existing Generic VC settings entry
     *
     * @param Settings 	$settings
     *
     * @return null
     */
    public function update(Settings $settings)
    {
        $this->generic_db->update($settings);
    }

    /**
     * Get Generic VC Settings
     *
     * @return Settings
     */
    public function select()
    {
        return $this->generic_db->select($this->getObjectId());
    }

    /**
     * Delete Generic VC settings entry
     *
     * @return null
     */
    public function delete()
    {
        $this->generic_db->delete($this->getObjectId());
    }

    /**
     * @inheritdoc
     */
    public function getMinutesRequired()
    {
        return $this->select()->getMinutesRequired();
    }

    /**
     * @inheritdoc
     */
    public function createUnkownParticipant($user_name, $email, $phone, $company, $minutes, $user_id = null)
    {
        assert('is_string($user_name)');
        assert('is_string($email)');
        assert('is_string($phone)');
        assert('is_string($company)');
        assert('is_int($minutes)');
        assert('is_null($user_id) || is_int($user_id)');

        $this->generic_db->createUnkownParticipant($this->getObjectId(), $user_name, $email, $phone, $company, $minutes, $user_id);
    }

    /**
     * Delete unknown participants
     *
     * @return null
     */
    public function deleteUnkownParticipants()
    {
        $this->generic_db->deleteUnkownParticipants($this->getObjectId());
    }

    /**
     * Delete unknown participant
     *
     * @param int 	$id
     *
     * @return null
     */
    public function deleteUnknownParticipant($id)
    {
        assert('is_int($id)');
        $this->generic_db->deleteUnkownParticipant($id);
    }

    /**
     * Set minutes of booked users to null
     *
     * @return void
     */
    public function resetMinutesOfBookedUsers()
    {
        $this->generic_db->resetMinutesOfBookedUsers($this->getObjectId());
    }

    /**
     * Get all unknown participants for current object
     *
     * @return Participant[]
     */
    public function getUnkownParticipants()
    {
        return $this->generic_db->getUnkownParticipants($this->getObjectId());
    }

    /**
     * @inheritdoc
     */
    public function updateParticipant(VC\Participant $participant)
    {
        return $this->generic_db->updateParticipant($participant);
    }

    /**
     * @inheritdoc
     */
    public function getParticipantByUserName($user_name)
    {
        assert('is_string($user_name)');
        return $this->generic_db->getParticipantByUserName($this->getObjectId(), $user_name, $this->config->getPhoneType());
    }

    /**
     * Get current object
     *
     * @return \ilObjWebinar
     */
    public function getObject()
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
    public function getAllParticipants()
    {
        $booked = $this->getBookedParticipants();
        $unknown = $this->generic_db->getUnkownParticipants($this->getObjectId());

        return array_merge($booked, $unknown);
    }

    /**
     * Get booked participants
     *
     * @return Participant[]
     */
    public function getBookedParticipants()
    {
        return $this->generic_db->getBookedParticipants($this->getObjectId(), $this->config->getPhoneType());
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
    public function getUnknownParticipantByLogin($user_name)
    {
        assert('is_string($user_name)');
        return $this->generic_db->getUnknownParticipantByLogin($this->getObjectId(), $user_name);
    }
}
