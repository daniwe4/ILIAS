<?php

namespace CaT\Plugins\BookingModalities\Steps;

use \ILIAS\TMS\Booking;

class SelfParallelCourseStep extends ParallelCourseStep implements Booking\SelfBookingStep, Booking\SelfBookingWithApprovalsStep
{
    /**
     * Find out if this step is applicable for the booking process of the
     * given user.
     *
     * @param	int	$usr_id
     * @return	bool
     */
    public function isApplicableFor($usr_id)
    {
        $course = $this->entity->object();
        $parallel_courses = $this->getParallelCoursesOfUser($course, $usr_id);
        $parallel_waiting = $this->getParallelWaitingListCoursesOfUser($course, $usr_id);

        require_once("Modules/Course/classes/class.ilCourseParticipants.php");
        require_once("Services/Membership/classes/class.ilWaitingList.php");
        require_once(__DIR__ . "/../Settings/class.ilBookingModalitiesGUI.php");

        return !\ilCourseParticipants::_isParticipant($course->getRefId(), $usr_id)
                && !\ilWaitingList::_isOnList($usr_id, $course->getId())
                && (count($parallel_courses) > 0 || count($parallel_waiting) > 0)
                && $this->owner->getBooking()->getModus() == \ilBookingModalitiesGUI::SELF_BOOKING;
    }
}
