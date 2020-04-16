<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */
/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace CaT\Plugins\BookingModalities\CourseActions;

use ILIAS\TMS;
use \ILIAS\TMS\MyUsersHelper;

/**
 * This is an information about a course action, noteworthy for a user in some context.
 */
class SuperiorBookCourseWithApproval extends TMS\CourseActionImpl
{
    use MyUsersHelper;

    /**
     * @inheritdoc
     */
    public function isAllowedFor($usr_id)
    {
        $course = $this->entity->object();

        $crs_start = $course->getCourseStart();
        $crs_member = $course->getMembersObject()->getCountMembers();

        $max_member = $this->owner->getMember()->getMax();
        $booking_deadline = $this->owner->getBooking()->getDeadline();
        $booking_beginning = $this->owner->getBooking()->getBeginning();

        $approval_required = count($this->owner->getApproversPositions()) > 0;
        $usr_booked = $course->getMembersObject()->isMember($usr_id);
        $usr_on_waiting = \ilWaitingList::_isOnList($usr_id, $course->getId());

        $booking_approval_ref_id = $this->owner->getApprovalObjectRefId();
        $usr_approval = false;
        if (!is_null($booking_approval_ref_id)) {
            $usr_approval = $this->checkApprovalState($usr_id, (int) $course->getId());
        }

        return $this->isSuperiorOf($usr_id) &&
            $this->bookable($crs_member, $max_member) &&
            $this->isInBookingPeriod($crs_start, $booking_beginning, $booking_deadline) &&
            $approval_required &&
            $this->owner->isSuperiorBooking() &&
            !$usr_booked &&
            !$usr_on_waiting &&
            !is_null($booking_approval_ref_id) &&
            !$this->userIsAnonymous($this->current_user_id) &&
            !$usr_approval
        ;
    }

    /**
     * @inheritdoc
     */
    public function getLink(\ilCtrl $ctrl, $usr_id)
    {
        $course = $this->entity->object();

        $ctrl->setParameterByClass("ilSuperiorBookingWithApproveGUI", "ref_id", $this->owner->getApprovalObjectRefId());
        $ctrl->setParameterByClass("ilSuperiorBookingWithApproveGUI", "crs_ref_id", $course->getRefId());
        $ctrl->setParameterByClass("ilSuperiorBookingWithApproveGUI", "usr_id", $usr_id);
        $link = $ctrl->getLinkTargetByClass(
            array(
                "ilObjPluginDispatchGUI",
                "ilObjBookingApprovalsGUI",
                "ilSuperiorBookingWithApproveGUI"
            ),
            "start"
        );
        $ctrl->setParameterByClass("ilSuperiorBookingWithApproveGUI", "ref_id", null);
        $ctrl->setParameterByClass("ilSuperiorBookingWithApproveGUI", "crs_ref_id", null);
        $ctrl->setParameterByClass("ilSuperiorBookingWithApproveGUI", "usr_id", null);

        return $link;
    }

    /**
     * Get status of booking for this course
     *
     * @param int 	$crs_member
     * @param int 	$max_member
     *
     * @return string
     */
    protected function bookable($crs_member, $max_member)
    {
        if ($max_member === null || ($crs_member < $max_member)) {
            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        $txt = $this->owner->txtClosure();
        return $txt("book_employee_course_with_approval");
    }

    /**
     * Is today in booking period of course
     *
     * @param ilDateTime 	$crs_start
     * @param int 	$booking_start
     * @param int 	$booking_end
     *
     * @return bool
     */
    public function isInBookingPeriod(\ilDateTime $crs_start = null, $booking_start, $booking_end)
    {
        if ($crs_start == null) {
            return true;
        }

        $today_string = date("Y-m-d");

        $booking_start_date = clone $crs_start;
        $booking_start_date->increment(\ilDateTime::DAY, -1 * $booking_start);
        $start_string = $booking_start_date->get(IL_CAL_DATE);

        $booking_end_date = clone $crs_start;
        $booking_end_date->increment(\ilDateTime::DAY, -1 * $booking_end);
        $end_string = $booking_end_date->get(IL_CAL_DATE);

        if ($today_string >= $start_string && $today_string <= $end_string) {
            return true;
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

    protected function checkApprovalState(int $usr_id, int $crs_id)
    {
        return $this->owner->checkApprovalStateFor($usr_id, $crs_id);
    }
}
