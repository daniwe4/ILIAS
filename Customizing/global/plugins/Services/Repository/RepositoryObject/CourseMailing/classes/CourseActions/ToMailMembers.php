<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace CaT\Plugins\CourseMailing\CourseActions;

use ILIAS\TMS;

/**
 * This is an information about a course action, noteworthy for a user in some context.
 */
class ToMailMembers extends TMS\CourseActionImpl
{
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
        return $this->owner->getLinkToMailMembers();
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        $txt = $this->owner->txtClosure();
        return $txt("to_mail_members");
    }

    /**
     * Check user has access to course member object
     *
     * @param int 	$cm_ref_id
     *
     * @return bool
     */
    protected function hasAccess($cm_ref_id)
    {
        global $DIC;
        $access = $DIC->access();
        if (
            $access->checkAccess("read", "", $cm_ref_id) &&
            $access->checkAccess("visible", "", $cm_ref_id) &&
            $access->checkAccess("mail_to_members", "", $cm_ref_id)
        ) {
            return true;
        }

        return false;
    }
}
