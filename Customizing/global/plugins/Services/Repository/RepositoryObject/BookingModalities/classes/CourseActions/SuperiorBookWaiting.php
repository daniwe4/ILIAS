<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace CaT\Plugins\BookingModalities\CourseActions;

use ILIAS\TMS;
use \ILIAS\TMS\MyUsersHelper;

/**
 * This is an information about a course action, noteworthy for a user in some context.
 */
class SuperiorBookWaiting extends TMS\CourseActionImpl
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

        require_once("Services/Membership/classes/class.ilWaitingList.php");
        $crs_waiting_member = \ilWaitingList::lookupListSize($course->getId());

        $max_member = $this->owner->getMember()->getMax();
        $with_waiting_list = $this->owner->getWaitinglist()->getModus() != "no_waitinglist";
        $max_waiting = $this->owner->getWaitinglist()->getMax();
        $booking_deadline = $this->owner->getBooking()->getDeadline();
        $booking_beginning = $this->owner->getBooking()->getBeginning();
        $modus = $this->owner->getBooking()->getModus();

        require_once(__DIR__ . "/../Settings/class.ilBookingModalitiesGUI.php");
        $is_superior_booking = $modus === \ilBookingModalitiesGUI::SUPERIOR_BOOKING;

        $approval_required = count($this->owner->getApproversPositions()) > 0;
        $usr_on_waiting = \ilWaitingList::_isOnList($usr_id, $course->getId());
        $usr_booked = $course->getMembersObject()->isMember($usr_id);

        return $this->isSuperiorOf($usr_id) &&
            $this->bookable($crs_member, $max_member, $crs_waiting_member, $max_waiting, $with_waiting_list) &&
            $this->isInBookingPeriod($crs_start, $booking_beginning, $booking_deadline) &&
            $is_superior_booking &&
            !$usr_booked &&
            !$usr_on_waiting &&
            !$approval_required &&
            !$this->userIsAnonymous($this->current_user_id)
        ;
    }

    /**
     * @inheritdoc
     */
    public function getLink(\ilCtrl $ctrl, $usr_id)
    {
        $course = $this->entity->object();

        $ctrl->initBaseClass("ilTrainingSearchGUI");
        $ctrl->setParameterByClass("ilTMSSuperiorBookWaitingGUI", "crs_ref_id", $course->getRefId());
        $ctrl->setParameterByClass("ilTMSSuperiorBookWaitingGUI", "usr_id", $usr_id);
        $link = $ctrl->getLinkTargetByClass(array("ilTrainingSearchGUI", "ilTMSSuperiorBookWaitingGUI"), "start");
        $ctrl->setParameterByClass("ilTMSSuperiorBookWaitingGUI", "crs_ref_id", null);
        $ctrl->setParameterByClass("ilTMSSuperiorBookWaitingGUI", "usr_id", null);

        return $link;
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        $txt = $this->owner->txtClosure();
        return $txt("book_employee_course_waiting");
    }

    /**
     * Get status of booking for this course
     *
     * @param int 	$crs_member
     * @param int 	$max_member
     * @param int 	$crs_waiting_member
     * @param int 	$max_waiting
     * @param bool 	$with_waiting_list
     *
     * @return string
     */
    protected function bookable($crs_member, $max_member, $crs_waiting_member, $max_waiting, $with_waiting_list)
    {
        return $max_member !== null &&
             ($crs_member >= $max_member) &&
             $with_waiting_list &&
             $crs_waiting_member < $max_waiting
        ;
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
}
