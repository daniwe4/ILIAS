<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace CaT\Plugins\BookingModalities\CourseActions;

use ILIAS\TMS;
use \ILIAS\TMS\MyUsersHelper;

/**
 * This is an information about a course action, noteworthy for a user in some context.
 */
class SuperiorBookRequest extends TMS\CourseActionImpl
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
        $booking_deadline = $this->owner->getBooking()->getDeadline();
        $max_member = $this->owner->getMember()->getMax();
        $admins = $this->getActiveAdmins($course);
        $usr_booked = $course->getMembersObject()->isMember($usr_id);
        $usr_on_waiting = \ilWaitingList::_isOnList($usr_id, $course->getId());
        $modus = $this->owner->getBooking()->getModus();

        require_once(__DIR__ . "/../Settings/class.ilBookingModalitiesGUI.php");
        $is_superior_booking = $modus === \ilBookingModalitiesGUI::SUPERIOR_BOOKING;

        return $this->isSuperiorOf($usr_id) &&
            $this->bookingPeriodPassed($booking_deadline, $crs_start) &&
            !$this->courseOverbooked($crs_member, $max_member) &&
            !$usr_booked &&
            !$usr_on_waiting &&
            count($admins) > 0 &&
            $is_superior_booking &&
            !$this->userIsAnonymous($this->current_user_id)
        ;
    }

    /**
     * @inheritdoc
     */
    public function getLink(\ilCtrl $ctrl, $usr_id)
    {
        $course = $this->entity->object();
        $admins = $this->getActiveAdmins($course);

        $admin_mails = array_map(
            function (\ilObjUser $admin) {
                return $admin->getEmail();
            },
            $admins
        );

        return "mailto:" . join(";", $admin_mails);
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        $txt = $this->owner->txtClosure();
        return $txt("superior_request_book");
    }

    /**
     * Is booking period passed
     *
     * @param int 	$booking_deadline
     * @param ilDateTime | null 	$crs_start
     *
     * @return bool
     */
    protected function bookingPeriodPassed($booking_deadline, \ilDateTime $crs_start = null)
    {
        if ($crs_start == null) {
            return false;
        }

        $today_string = date("Y-m-d");

        $booking_end_date = clone $crs_start;
        $booking_end_date->increment(\ilDateTime::DAY, -1 * $booking_deadline);
        $end_string = $booking_end_date->get(IL_CAL_DATE);

        if ($end_string >= $today_string) {
            return false;
        }

        return true;
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

    /**
     * Checks the course is overbooked or not
     *
     * @param int 	$crs_member
     * @param int 	$max_member
     *
     * @return bool
     */
    protected function courseOverbooked($crs_member, $max_member)
    {
        return $crs_member >= $max_member;
    }

    protected function getAccess()
    {
        if (is_null($this->access)) {
            global $DIC;
            $this->access = $DIC["ilAccess"];
        }

        return $this->access;
    }

    protected function getActiveAdmins(\ilObjCourse $course) : array
    {
        $admin_ids = $course->getMembersObject()->getAdmins();

        if (!is_array($admin_ids) ||
            count($admin_ids) == 0
        ) {
            return [];
        }

        return array_filter(
            array_map(
                function ($ad) {
                    return new \ilObjUser($ad);
                },
                $admin_ids
            ),
            function (\ilObjUser $admin) {
                return $admin->getActive();
            }
        );
    }

    protected function userIsAnonymous($usr_id)
    {
        return $usr_id == ANONYMOUS_USER_ID;
    }
}
