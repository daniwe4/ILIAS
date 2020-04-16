<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */
namespace CaT\Plugins\CourseMember\CourseActions;

use ILIAS\TMS;

/**
 * This is an information about a course action, noteworthy for a user in some context.
 */
class ToCourseMember extends TMS\CourseActionImpl
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
        return $this->owner->getLinkToMemberView();
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        global $DIC;
        $txt = $this->owner->txtClosure();
        return $txt("to_course_member");
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
        if ($access->checkAccess("read", "", $cm_ref_id)
            && $access->checkAccess("view_lp", "", $cm_ref_id)
        ) {
            return true;
        }

        return false;
    }
}
