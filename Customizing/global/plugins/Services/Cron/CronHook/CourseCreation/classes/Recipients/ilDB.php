<?php

declare(strict_types=1);

namespace CaT\Plugins\CourseCreation\Recipients;

use \ilSetting;

class ilDB implements DB
{
    const KEY_FAILED_RECIPIENTS = "xccr_failed_recipients";

    /**
     * @var \lSetting
     */
    protected $setting;

    public function __construct(ilSetting $setting)
    {
        $this->setting = $setting;
    }

    /**
     * @param string[]
     */
    public function saveRecipients(array $recipients)
    {
        $this->setting->set(self::KEY_FAILED_RECIPIENTS, serialize($recipients));
    }

    /**
     * @return Recipient[]
     */
    public function getRecipients() : array
    {
        $ret = [];
        foreach ($this->readSettings() as $recipient) {
            $user_id = (int) \ilObjUser::_lookupId($recipient);
            $ret[] = new Recipient($user_id, $recipient);
        }

        return $ret;
    }

    /**
     * @return string[]
     */
    public function getRecipientsForForm() : array
    {
        return $this->readSettings();
    }

    protected function readSettings() : array
    {
        $vals = $this->setting->get(self::KEY_FAILED_RECIPIENTS, []);

        if (is_array($vals)) {
            return $vals;
        }

        return unserialize($vals);
    }
}
