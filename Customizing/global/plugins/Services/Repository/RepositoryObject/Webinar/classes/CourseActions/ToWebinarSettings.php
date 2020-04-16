<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace CaT\Plugins\Webinar\CourseActions;

use ILIAS\TMS;
use \ILIAS\TMS\MyUsersHelper;

/**
 * This is an information about a course action, noteworthy for a user in some context.
 */
class ToWebinarSettings extends TMS\CourseActionImpl
{
    use MyUsersHelper;

    /**
     * @inheritdoc
     */
    public function isAllowedFor($usr_id)
    {
        return $this->hasAccess($this->owner->getRefId());
    }

    /**
     * @inheritdoc
     */
    public function getLink(\ilCtrl $ctrl, $usr_id)
    {
        return $this->owner->getLinkToSettings();
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        $txt = $this->owner->txtClosure();
        return $txt("to_webinar_settings");
    }

    /**
     * Has user read access to the course
     *
     * @param int 	$crs_ref_id
     *
     * @return bool
     */
    protected function hasAccess($crs_ref_id)
    {
        $access = $this->getAccess();
        $course = $this->entity->object();

        if ($access->checkAccess("visible", "", $crs_ref_id)
            && $access->checkAccess("read", "", $crs_ref_id)
            && $access->checkAccess("write", "", $crs_ref_id)
        ) {
            return true;
        }

        return false;
    }

    protected function getAccess()
    {
        if (is_null($this->access)) {
            global $DIC;
            $this->access = $DIC["ilAccess"];
        }

        return $this->access;
    }
}
