<?php

namespace CaT\Plugins\CourseMailing\Settings;

/**
 * Simple class to hold infos about log entries
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class LogEntry
{
    /**
     * @var int
     */
    protected $user_id;

    /**
     * @var \DateTime
     */
    protected $change_date;

    /**
     * @var int
     */
    protected $value_invite;

    /**
     * @var int
     */
    protected $value_invitereminder;

    /**
     * @var bool
     */
    protected $value_supress;

    public function __construct(
        int $user_id,
        \DateTime $change_date,
        int $value_invite,
        int $value_invitereminder,
        bool $value_supress
    ) {
        $this->user_id = $user_id;
        $this->change_date = $change_date;
        $this->value_invite = $value_invite;
        $this->value_invitereminder = $value_invitereminder;
        $this->value_supress = $value_supress;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @return \DateTime
     */
    public function getChangeDate()
    {
        return $this->change_date;
    }

    /**
     * @return int
     */
    public function getValueInvite()
    {
        return $this->value_invite;
    }

    /**
     * @return int
     */
    public function getValueInvitereminder()
    {
        return $this->value_invitereminder;
    }

    /**
     * @return bool
     */
    public function getValueSupress()
    {
        return $this->value_supress;
    }
}
