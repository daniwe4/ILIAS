<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace CaT\Plugins\BookingModalities\CourseActions;

use ILIAS\TMS;
use \ILIAS\TMS\MyUsersHelper;

/**
 * This is an information about a course action, noteworthy for a user in some context.
 */
class SuperiorBookCourse extends TMS\CourseActionImpl
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
        $modus = $this->owner->getBooking()->getModus();

        require_once(__DIR__ . "/../Settings/class.ilBookingModalitiesGUI.php");
        $is_superior_booking = $modus === \ilBookingModalitiesGUI::SUPERIOR_BOOKING;

        $approval_required = count($this->owner->getApproversPositions()) > 0;
        $usr_booked = $course->getMembersObject()->isMember($usr_id);
        $usr_on_waiting = \ilWaitingList::_isOnList($usr_id, $course->getId());

        return $this->isSuperiorOf($usr_id) &&
            $this->bookable($crs_member, $max_member) &&
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

        $ctrl->setParameterByClass("ilTMSSuperiorBookingGUI", "crs_ref_id", $course->getRefId());
        $ctrl->setParameterByClass("ilTMSSuperiorBookingGUI", "usr_id", $usr_id);
        $link = $ctrl->getLinkTargetByClass(array("ilPersonalDesktopGUI", "ilTrainingSearchGUI", "ilTMSSuperiorBookingGUI"), "start");
        $ctrl->setParameterByClass("ilTMSSuperiorBookingGUI", "crs_ref_id", null);
        $ctrl->setParameterByClass("ilTMSSuperiorBookingGUI", "usr_id", null);

        return $link;
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        $txt = $this->owner->txtClosure();
        return $txt("book_employee_course");
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
