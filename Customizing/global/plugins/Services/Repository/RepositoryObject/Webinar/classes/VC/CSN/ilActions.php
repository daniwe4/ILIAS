<?php
namespace CaT\Plugins\Webinar\VC\CSN;

use CaT\Plugins\Webinar\VC;
use CaT\Plugins\Webinar\Config\Config;
use CaT\Libs\ExcelWrapper\Spout;

/**
 * Actions for CSN VC
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

    public function __construct(\ilObjWebinar $object, ilDB $csn_db, Config $config)
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
    public function create($phone = null, $pin = null, $minutes_required = null)
    {
        assert('is_string($phone) || is_null($phone)');
        assert('is_string($pin) || is_null($pin)');
        assert('is_int($minutes_required) || is_null($minutes_required)');

        return $this->csn_db->create($this->getObjectId(), $phone, $pin, $minutes_required);
    }

    /**
     * Update an existing CSN VC settings entry
     *
     * @param Settings 	$settings
     *
     * @return null
     */
    public function update(Settings $settings)
    {
        $this->csn_db->update($settings);
    }

    /**
     * Get CSN VC Settings
     *
     * @return Settings
     */
    public function select()
    {
        return $this->csn_db->select($this->getObjectId());
    }

    /**
     * Delete CSN VC settings entry
     *
     * @return null
     */
    public function delete()
    {
        $this->csn_db->delete($this->getObjectId());
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

        $this->csn_db->createUnkownParticipant($this->getObjectId(), $user_name, $email, $phone, $company, $minutes, $user_id);
    }

    /**
     * Delete unknown participants
     *
     * @return null
     */
    public function deleteUnkownParticipants()
    {
        $this->csn_db->deleteUnkownParticipants($this->getObjectId());
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
        $this->csn_db->deleteUnkownParticipant($id);
    }

    /**
     * Set minutes of booked users to null
     *
     * @return void
     */
    public function resetMinutesOfBookedUsers()
    {
        $this->csn_db->resetMinutesOfBookedUsers($this->getObjectId());
    }

    /**
     * Get all unknown participants for current object
     *
     * @return Participant[]
     */
    public function getUnkownParticipants()
    {
        return $this->csn_db->getUnkownParticipants($this->getObjectId());
    }

    /**
     * @inheritdoc
     */
    public function updateParticipant(VC\Participant $participant)
    {
        return $this->csn_db->updateParticipant($participant);
    }

    /**
     * @inheritdoc
     */
    public function getParticipantByUserName($user_name)
    {
        assert('is_string($user_name)');
        return $this->csn_db->getParticipantByUserName($this->getObjectId(), $user_name, $this->config->getPhoneType());
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
        $unknown = $this->csn_db->getUnkownParticipants($this->getObjectId());

        return array_merge($booked, $unknown);
    }

    /**
     * Get booked participants
     *
     * @return Participant[]
     */
    public function getBookedParticipants()
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
    public function getUnknownParticipantByLogin($user_name)
    {
        assert('is_string($user_name)');
        return $this->csn_db->getUnknownParticipantByLogin($this->getObjectId(), $user_name);
    }
}
