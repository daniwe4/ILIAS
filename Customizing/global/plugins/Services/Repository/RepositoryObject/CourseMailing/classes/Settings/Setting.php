<?php
namespace CaT\Plugins\CourseMailing\Settings;

/**
 * This holds forther settings for this object.
 */
class Setting
{

    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var int
     */
    protected $days_invitation;

    /**
     * @var int
     */
    protected $days_invitation_reminder;

    /**
     * @var bool
     */
    protected $prevent_mailing;

    /**
     * @param int 	$obj_id
     * @param int  	$days_invitation
     * @param int  	$days_invitation_reminder
     * @param bool 	$prevent_mailing
     */
    public function __construct(
        $obj_id,
        $days_invitation,
        $days_invitation_reminder,
        $prevent_mailing
    ) {
        assert('is_int($obj_id)');
        assert('is_int($days_invitation)');
        assert('is_int($days_invitation_reminder)');
        assert('is_bool($prevent_mailing)');

        $this->obj_id = $obj_id;
        $this->days_invitation = $days_invitation;
        $this->days_invitation_reminder = $days_invitation_reminder;
        $this->prevent_mailing = $prevent_mailing;
    }

    /**
     * @return int
     */
    public function getObjectId()
    {
        return $this->obj_id;
    }

    /**
     * @return int
     */
    public function getDaysInvitation()
    {
        return $this->days_invitation;
    }

    /**
     * @return int
     */
    public function getDaysRemindInvitation()
    {
        return $this->days_invitation_reminder;
    }

    /**
     * @return bool
     */
    public function getPreventMailing()
    {
        return $this->prevent_mailing;
    }

    /**
     * @param int 	$days
     * @return Setting
     */
    public function withDaysInvitation($days)
    {
        assert('is_int($days)');
        $clone = clone $this;
        $clone->days_invitation = $days;
        return $clone;
    }

    /**
     * @param int 	$days
     * @return Setting
     */
    public function withDaysRemindInvitation($days)
    {
        assert('is_int($days)');
        $clone = clone $this;
        $clone->days_invitation_reminder = $days;
        return $clone;
    }

    /**
     * @param 	bool 	$value
     * @return 	Setting
     */
    public function withPreventMailing($value)
    {
        assert('is_bool($value)');
        $clone = clone $this;
        $clone->prevent_mailing = $value;
        return $clone;
    }
}
