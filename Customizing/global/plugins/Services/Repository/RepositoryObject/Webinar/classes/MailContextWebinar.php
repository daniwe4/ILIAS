<?php

namespace CaT\Plugins\Webinar;

require_once('./Services/TMS/Mailing/classes/class.ilTMSMailContextEnte.php');
require_once('./Services/TMS/Mailing/classes/class.ilTMSMailContextUser.php');

class MailContextWebinar extends \ilTMSMailContextEnte
{
    protected static $PLACEHOLDERS = array(
        'WEBINAR_LINK' => 'placeholder_desc_link',
        'WEBINAR_LOGIN_TRAINER' => 'placeholder_desc_login_trainer',
        'WEBINAR_PASSWORD' => 'placeholder_desc_password',
        'WEBINAR_PASSWORD_TRAINER' => 'placeholder_desc_password_trainer',
        'WEBINAR_PIN' => 'placeholder_desc_pin'
    );

    /**
     * @inheritdoc
     */
    public function placeholderIds()
    {
        return array_keys(self::$PLACEHOLDERS);
    }

    /**
     * @inheritdoc
     */
    public function placeholderDescriptionForId(string $placeholder_id) : string
    {
        $desc = self::$PLACEHOLDERS[$placeholder_id];
        $obj = \ilPluginAdmin::getPluginObjectById("xwbr");
        return $obj->txt($desc);
    }

    /**
     * Get id of the parent plugin
     *
     * @return string
     */
    public function getPluginObject()
    {
        return \ilPluginAdmin::getPluginObjectById("xwbr");
        ;
    }

    /**
     * @inheritdoc
     */
    public function valueFor(string $placeholder_id, array $contexts = array()) : ?string
    {
        if (!in_array($placeholder_id, $this->placeholderIds())) {
            return null;
        }

        return $this->resolveValueFor($placeholder_id, $contexts);
    }

    /**
     * @param string $id
     * @param MailContexts[] $contexts
     * @return $string
     */
    protected function resolveValueFor($id, $contexts)
    {
        $actions = $this->owner->getActions();
        $obj = $actions->getObject();
        $settings = $obj->getSettings();

        if ($id === 'WEBINAR_LINK') {
            return $settings->getUrl();
        }

        $vcsettings = $obj->getVCActions()->select();

        if ($settings->getVCType() === 'Generic') {
            switch ($id) {
                case 'WEBINAR_PASSWORD':
                    return $vcsettings->getPassword();
                    break;
                case 'WEBINAR_LOGIN_TRAINER':
                    return $vcsettings->getTutorLogin();
                    break;
                case 'WEBINAR_PASSWORD_TRAINER':
                    return $vcsettings->getTutorPassword();
                    break;

                default:
                    return null;
            }
        }
        if ($settings->getVCType() === 'CSN') {
            switch ($id) {
                case 'WEBINAR_PIN':
                    return $vcsettings->getPin();
                    break;

                default:
                    return null;
            }
        }

        return null;
    }
}
