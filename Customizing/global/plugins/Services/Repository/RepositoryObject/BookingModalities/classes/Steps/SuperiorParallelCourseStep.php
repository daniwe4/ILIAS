<?php

namespace CaT\Plugins\BookingModalities\Steps;

use \ILIAS\TMS\Booking;

class SuperiorParallelCourseStep extends ParallelCourseStep implements Booking\SuperiorBookingStep, Booking\SuperiorBookingWithApprovalsStep
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
                && $this->owner->getBooking()->getModus() == \ilBookingModalitiesGUI::SUPERIOR_BOOKING;
    }

    /**
     * @inheritdoc
     */
    protected function getInfoForStornoMessage()
    {
        return $this->txt("superior_info_can_storno");
    }

    /**
     * @inheritdoc
     */
    protected function getParallelCoursesConfirmationMessage()
    {
        return $this->txt("superior_parallel_courses_confirmation");
    }

    /**
     * @inheritdoc
     */
    protected function getParallelCoursesConfirmationAlertMessage()
    {
        return $this->txt("superior_parallel_courses_confirmation_alert");
    }
}
