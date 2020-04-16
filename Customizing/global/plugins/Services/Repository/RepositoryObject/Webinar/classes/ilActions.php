<?php

namespace CaT\Plugins\Webinar;

/**
 * Communication class between front- and backend.
 * E.g. GUI only use this class to get information from ILIAS DB.
 */
class ilActions
{
    /**
     * @var \ilObjWebinar
     */
    protected $object;

    /**
     * @var Settings\DB
     */
    protected $settings_db;

    /**
     * @var Participants\DB
     */
    protected $participants_db;

    /**
     * @var LPSettings\LPManager
     */
    protected $lp_manager;

    public function __construct(\ilObjWebinar $object, Settings\DB $settings_db, Participant\DB $participants_db, LPSettings\LPManager $lp_manager)
    {
        $this->object = $object;
        $this->settings_db = $settings_db;
        $this->participants_db = $participants_db;
        $this->lp_manager = $lp_manager;
    }

    /**
     * Get settings for current object
     *
     * @return Settings\Webinar
     */
    public function select()
    {
        return $this->settings_db->selectFor($this->getObject()->getId());
    }

    /**
     * Update settings for current object
     *
     * @param Settings\Webinar 	$webinar
     *
     * @return null
     */
    public function update(Settings\Webinar $webinar)
    {
        $this->settings_db->update($webinar);
    }

    /**
     * Create a new settings for current object.
     *
     * @param string 	$vc_type
     * @param \ilDateTime | null 	$beginning
     * @param \ilDateTime | null 	$ending
     * @param string | null 	$admission
     * @param string | null 	$url
     * @param bool 	$online
     *
     * @return Settings\Webinar
     */
    public function create(
        $vc_type,
        \ilDateTime $beginning = null,
        \ilDateTime $ending = null,
        $admission = null,
        $url = null,
        $online = false
    ) {
        assert('is_string($vc_type)');
        assert('is_string($admission) | is_null($url)');
        assert('is_string($url) | is_null($url)');
        assert('is_bool($online) | is_null($url)');

        return $this->settings_db->create(
            (int) $this->getObject()->getId(),
            $vc_type,
            $beginning,
            $ending,
            $admission,
            $url,
            $online
        );
    }

    /**
     * Delete settings for current object
     *
     * @return null
     */
    public function delete()
    {
        $this->settings_db->deleteFor($this->getObject()->getId());
        $this->participants_db->deleteFor($this->getObject()->getId());
    }

    /**
     * Book new user on current object
     *
     * @param int 	$user_id
     * @param string 	$user_name
     *
     * @return null
     */
    public function bookParticipant($user_id, $user_name)
    {
        assert('is_int($user_id)');
        assert('is_string($user_name)');
        $this->participants_db->book(
            $this->getObject()->getId(),
            $user_id,
            $user_name
        );
    }

    public function portUserToBookParticipant($user_id, $unknown_user)
    {
        assert('is_int($user_id)');
        $this->participants_db->book(
            $this->getObject()->getId(),
            $user_id,
            $unknown_user->getUserName(),
            $unknown_user->getMinutes()
        );
    }

    /**
     * Cancel booking of user for current object
     *
     * @param int 	$user_id
     *
     * @return null
     */
    public function cancelParticipitation($user_id)
    {
        assert('is_int($user_id)');
        $this->participants_db->cancel(
            $this->getObject()->getId(),
            $user_id
        );
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
     * Refresh LP state for all known members
     *
     * @return null
     */
    public function refreshLP()
    {
        $this->lp_manager->refresh((int) $this->getObject()->getId());
    }

    /**
     * Check current user is booked
     *
     * @param int 	$user_id
     *
     * @return bool
     */
    public function isBookedUser($user_id)
    {
        assert('is_int($user_id)');

        return $this->participants_db->isBookedUser((int) $this->getObject()->getId(), $user_id);
    }

    /**
     * Get ids of all booked users
     *
     * @return int[]
     */
    public function getAllBookedUserIds()
    {
        return $this->participants_db->getAllBookedUserIds((int) $this->getObject()->getId());
    }

    /**
     * Check current user is tutor
     *
     * @param int 	$user_id
     *
     * @return bool
     */
    public function isTutor($user_id)
    {
        assert('is_int($user_id)');

        $parent_crs = $this->object->getParentCourse();

        if (!$parent_crs) {
            return false;
        }

        return in_array($user_id, $parent_crs->getMembersObject()->getTutors());
    }

    /**
     * Set participation status
     *
     * @param int 	$user_id
     * @param bool 	$status
     *
     * @return null
     */
    public function setParticipationStatus($user_id, $status)
    {
        $this->participants_db->setParticipationStatus((int) $this->getObject()->getId(), $user_id, $status);
    }

    /**
     * Get the lp data for user
     *
     * @param int 	$user_id
     *
     * @return bool
     */
    public function getLPDataFor($user_id)
    {
        return $this->participants_db->getLPDataFor((int) $this->getObject()->getId(), $user_id);
    }
}
