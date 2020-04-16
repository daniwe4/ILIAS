<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace CaT\Plugins\BookingModalities\CourseActions;

use ILIAS\TMS;
use \ILIAS\TMS\MyUsersHelper;

/**
 * This is an information about a course action, noteworthy for a user in some context.
 */
class SuperiorCancelWaiting extends TMS\CourseActionImpl
{
    use MyUsersHelper;

    /**
     * @inheritdoc
     */
    public function isAllowedFor($usr_id)
    {
        $course = $this->entity->object();

        $crs_start = $course->getCourseStart();

        $storno_modus = $this->owner->getStorno()->getModus();
        $storno_deadline = $this->owner->getStorno()->getDeadline();
        $storno_hard_deadline = $this->owner->getStorno()->getHardDeadline();
        $is_booked = \ilCourseParticipants::_isParticipant($course->getRefId(), $usr_id);
        $is_waiting = \ilWaitingList::_isOnList($usr_id, $course->getId());

        return $this->isSuperiorOf($usr_id) &&
            $this->cancelable($crs_start, $storno_modus, $storno_deadline, $storno_hard_deadline) &&
            !$is_booked &&
            $is_waiting &&
            !$this->userIsAnonymous($this->current_user_id)
        ;
    }

    /**
     * @inheritdoc
     */
    public function getLink(\ilCtrl $ctrl, $usr_id)
    {
        $course = $this->entity->object();

        $ctrl->setParameterByClass("ilTMSSuperiorCancelWaitingGUI", "crs_ref_id", $course->getRefId());
        $ctrl->setParameterByClass("ilTMSSuperiorCancelWaitingGUI", "usr_id", $usr_id);
        $link = $ctrl->getLinkTargetByClass(array("ilSuperiorViewGUI", "ilTMSSuperiorCancelWaitingGUI"), "start");
        $ctrl->setParameterByClass("ilTMSSuperiorCancelWaitingGUI", "crs_ref_id", null);
        $ctrl->setParameterByClass("ilTMSSuperiorCancelWaitingGUI", "usr_id", null);

        return $link;
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        $txt = $this->owner->txtClosure();
        return $txt("cancel_employee_booking");
    }

    /**
     * Is the user able to cancel the course for employees
     *
     * @param ilDateTime | null 	$crs_start
     * @param string | null 	$storno_modus
     * @param int 	$storno_deadline
     * @param int 	$storno_hard_deadline
     *
     * @return string
     */
    protected function cancelable($crs_start, $storno_modus, $storno_deadline, $storno_hard_deadline)
    {
        require_once(__DIR__ . "/../Settings/class.ilBookingModalitiesGUI.php");
        if ($storno_modus !== null && $storno_modus == \ilBookingModalitiesGUI::SUPERIOR_CANCEL) {
            if ($crs_start === null) {
                return true;
            }

            $today = date("Y-m-d");
            $storno_end_date = clone $crs_start;
            if ($storno_deadline !== null && $storno_deadline > 0) {
                require_once("Services/Calendar/classes/class.ilDateTime.php");
                $storno_end_date->increment(\ilDateTime::DAY, -1 * $storno_deadline);
            }

            $hard_storno_end_date = clone $crs_start;
            if ($storno_hard_deadline !== null && $storno_hard_deadline > 0) {
                require_once("Services/Calendar/classes/class.ilDateTime.php");
                $hard_storno_end_date->increment(\ilDateTime::DAY, -1 * $storno_hard_deadline);
            }

            if ($today <= $storno_end_date->get(IL_CAL_DATE)) {
                return true;
            } elseif ($storno_hard_deadline == 0 || ($today <= $hard_storno_end_date->get(IL_CAL_DATE))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks the current user is superior of
     *
     * @param int 	$usr_id
     *
     * @return int
     */
    protected function isSuperiorOf($usr_id)
    {
        $members_below = $this->getUserWhereCurrentCanBookFor((int) $this->current_user_id);
        return array_key_exists($usr_id, $members_below);
    }

    protected function getAccess()
    {
        if (is_null($this->access)) {
            global $DIC;
            $this->access = $DIC["ilAccess"];
        }

        return $this->access;
    }

    protected function userIsAnonymous($usr_id)
    {
        return $usr_id == ANONYMOUS_USER_ID;
    }
}
