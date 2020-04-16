<?php

namespace CaT\Plugins\MaterialList\Settings;

class MaterialList
{
    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var \ilDateTime
     */
    protected $last_edit_datetime;

    /**
     * @var int
     */
    protected $last_edit_by;

    /**
     * @var string
     */
    protected $recipient_mode;

    /**
     * @var string
     */
    protected $recipient;

    /**
     * @var int
     */
    protected $send_days_before;

    public function __construct($obj_id, \ilDateTime $last_edit_datetime, $last_edit_by, $recipient_mode, $recipient = null, $send_days_before = null)
    {
        assert('is_int($obj_id)');
        assert('is_int($last_edit_by)');
        assert('is_string($recipient_mode)');
        assert('is_null($recipient) || is_string($recipient)');
        assert('is_null($send_days_before) || is_int($send_days_before)');

        $this->obj_id = $obj_id;
        $this->last_edit_datetime = $last_edit_datetime;
        $this->last_edit_by = $last_edit_by;
        $this->recipient_mode = $recipient_mode;
        $this->recipient = $recipient;
        $this->send_days_before = $send_days_before;
    }

    /**
     * Get the obj_id
     *
     * @return int
     */
    public function getObjId()
    {
        return $this->obj_id;
    }

    /**
     * Get the datetime of last edit
     *
     * @return \ilDateTime
     */
    public function getLastEditDateTime()
    {
        return $this->last_edit_datetime;
    }

    /**
     * Get last user id of last editor
     *
     * @return int
     */
    public function getLastEditBy()
    {
        return $this->last_edit_by;
    }

    /**
     * Get the mode to determine the recipient
     *
     * @return string
     */
    public function getRecipientMode()
    {
        return $this->recipient_mode;
    }

    /**
     * Get the recipient
     *
     * @return string
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * Get the days before course start list should be send
     *
     * @param int | null
     */
    public function getSendDaysBefore()
    {
        return $this->send_days_before;
    }

    /**
     * Set the datetime of last edit
     *
     * @param \ilDateTime 	$last_edit_datetime
     *
     * @return MaterialList
     */
    public function withLastEditDateTime(\ilDateTime $last_edit_datetime)
    {
        $clone = clone $this;
        $clone->last_edit_datetime = $last_edit_datetime;
        return $clone;
    }

    /**
     * Set user id of last edit user
     *
     * @param int 	$last_edit_by
     *
     * @return MaterialList
     */
    public function withLastEditBy($last_edit_by)
    {
        assert('is_int($last_edit_by)');
        $clone = clone $this;
        $clone->last_edit_by = $last_edit_by;
        return $clone;
    }

    /**
     * Get clone with recipient mode
     *
     * @param string 	$recipient_mode
     *
     * @return MaterialList
     */
    public function withRecipientMode($recipient_mode)
    {
        assert('is_string($recipient_mode)');
        $clone = clone $this;
        $clone->recipient_mode = $recipient_mode;
        return $clone;
    }

    /**
     * Get clone with recipient
     *
     * @param string | null 	$recipient
     *
     * @return MaterialList
     */
    public function withRecipient($recipient)
    {
        assert('is_null($recipient) || is_string($recipient)');
        $clone = clone $this;
        $clone->recipient = $recipient;
        return $clone;
    }

    /**
     * Get clone with send days before
     *
     * @param int | null 	$send_days_before
     *
     * @return MaterialList
     */
    public function withSendDaysBefore($send_days_before)
    {
        assert('is_null($send_days_before) || is_int($send_days_before)');
        $clone = clone $this;
        $clone->send_days_before = $send_days_before;
        return $clone;
    }
}
