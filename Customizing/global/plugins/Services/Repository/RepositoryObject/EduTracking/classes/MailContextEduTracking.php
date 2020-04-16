<?php

namespace CaT\Plugins\EduTracking;

require_once('./Services/TMS/Mailing/classes/class.ilTMSMailContextEnte.php');
require_once('./Services/TMS/Mailing/classes/class.ilTMSMailContextUser.php');

class MailContextEduTracking extends \ilTMSMailContextEnte
{
    protected static $PLACEHOLDERS = array(
        'IDD_TIME' => 'placeholder_desc_idd_time'
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
    public function placeholderDescriptionForId($placeholder_id)
    {
        $desc = self::$PLACEHOLDERS[$placeholder_id];
        $obj = \ilPluginAdmin::getPluginObjectById("xetr");
        return $obj->txt($desc);
    }

    /**
     * @inheritdoc
     */
    public function valueFor($placeholder_id, $contexts = array())
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
        $actions = $this->owner->getActionsFor("IDD");
        $settings = $actions->select();

        switch ($id) {
            case 'IDD_TIME':
                return $this->transformMinutes($settings->getMinutes()) . " " . $this->owner->pluginTxt("hours");
                break;
            default:
                return 'NOT RESOLVED: ' . $id;
        }
    }

    /**
     * Transforms the idd minutes into printable string
     *
     * @param int 	$minutes
     *
     * @return string
     */
    protected function transformMinutes($minutes)
    {
        if ($minutes === null) {
            return "";
        }

        $hours = floor($minutes / 60);
        $minutes = $minutes - $hours * 60;
        return str_pad($hours, "2", "0", STR_PAD_LEFT) . ":" . str_pad($minutes, "2", "0", STR_PAD_LEFT);
    }
}
