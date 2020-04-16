<?php

namespace CaT\Plugins\RoomSetup\Settings;

class RoomSetup
{
    const TYPE_SERVICE = 1;
    const TYPE_ROOMSETUP = 2;

    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var int
     */
    protected $setting_type;

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

    public function __construct($obj_id, $setting_type, $recipient_mode = null, $recipient = null, $send_days_before = null)
    {
        assert('is_int($obj_id)');
        assert('is_int($setting_type) && in_array($setting_type, self::getPossibleSettingTypes())');
        assert('is_null($recipient_mode) || is_string($recipient_mode)');
        assert('is_null($recipient) || is_string($recipient)');
        assert('is_null($send_days_before) || is_int($send_days_before)');

        $this->obj_id = $obj_id;
        $this->setting_type = $setting_type;
        $this->recipient_mode = $recipient_mode;
        $this->recipient = $recipient;
        $this->send_days_before = $send_days_before;
    }

    /**
     * Get all valid setting-types.
     *
     * @return int[]
     */
    public static function getPossibleSettingTypes()
    {
        return [
            self::TYPE_ROOMSETUP,
            self::TYPE_SERVICE
        ];
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
     * Get the type of the setting (TYPE_ROOMSETUP|TYPE_SERVICE)
     *
     * @return int
     */
    public function getType()
    {
        return $this->setting_type;
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
     * @param int
     */
    public function getSendDaysBefore()
    {
        return $this->send_days_before;
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
