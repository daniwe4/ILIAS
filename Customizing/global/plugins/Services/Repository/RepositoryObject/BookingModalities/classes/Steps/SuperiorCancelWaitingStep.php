<?php

namespace CaT\Plugins\BookingModalities\Steps;

use \ILIAS\TMS\Booking;

class SuperiorCancelWaitingStep extends CancelStep implements Booking\SuperiorBookingStep
{
    /**
     * @inheritdocs
     */
    public function getLabel()
    {
        return $this->txt("default_cancel_step_label");
    }

    /**
     * Get a description for this step in the process.
     *
     * @return	string
     */
    public function getDescription()
    {
        return $this->txt("unknown");
    }

    /**
     * Get the priority of the step.
     *
     * Lesser priorities means the step should be performed earlier.
     *
     * @return	int
     */
    public function getPriority()
    {
        return 10;
    }

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
        require_once("Modules/Course/classes/class.ilCourseParticipants.php");
        require_once("Services/Membership/classes/class.ilWaitingList.php");
        $is_booked = \ilCourseParticipants::_isParticipant($course->getRefId(), $usr_id);
        $is_waiting = \ilWaitingList::_isOnList($usr_id, $course->getId());

        $crs_start = $course->getCourseStart();
        $can_cancel = $this->canCancel($crs_start);

        return $can_cancel &&
            !$is_booked &&
            $is_waiting
        ;
    }

    /**
     * @inheritdoc
     */
    protected function getConfirmMessage()
    {
        return $this->txt("employee_confirmation_cancel");
    }

    /**
     * @inheritdoc
     */
    protected function getConfirmAlertMessage()
    {
        return $this->txt("employee_confirmation_cancel_alert");
    }

    /**
     * @inheritdoc
     */
    protected function requiredCancelMode()
    {
        require_once(__DIR__ . "/../Settings/class.ilBookingModalitiesGUI.php");
        return \ilBookingModalitiesGUI::SUPERIOR_CANCEL;
    }

    /**
     * @inheritdoc
     */
    protected function mightProcessed($usr_id)
    {
        $employees = $this->getUsersWhereCurrentCanViewBookings((int) $this->getActingUser()->getId());
        return array_key_exists($usr_id, $employees);
    }

    /**
     * @inheritdoc
     */
    protected function getCancelBookingDoneMessage($title, $usr_id)
    {
        $fullname = \ilObjUser::_lookupFullname($usr_id);
        return sprintf($this->txt("superior_booking_cancel_done"), $title, $fullname);
    }

    /**
     * @inheritdoc
     */
    protected function getCancelWaitingDoneMessage($title, $usr_id)
    {
        $fullname = \ilObjUser::_lookupFullname($usr_id);
        return sprintf($this->txt("superior_booking_cancel_waiting_list_done"), $title, $fullname);
    }
}
