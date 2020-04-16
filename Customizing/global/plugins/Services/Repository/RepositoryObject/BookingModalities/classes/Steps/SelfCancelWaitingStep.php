<?php

namespace CaT\Plugins\BookingModalities\Steps;

use \ILIAS\TMS\Booking;

class SelfCancelWaitingStep extends CancelStep implements Booking\SelfBookingStep
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
    protected function addConfirmCheckBox(\ilPropertyFormGUI $form)
    {
        if (!$this->owner->getBooking()->getHideSuperiorApprove()) {
            $online = new \ilCheckboxInputGUI("", self::CANCEL_CONFIRMATION_CHECKBOX);
            $online->setInfo($this->getConfirmMessage());
            $form->addItem($online);
        }
    }

    /**
     * @inheritdoc
     */
    protected function skipConfirmCheck()
    {
        return $this->owner->getBooking()->getHideSuperiorApprove();
    }

    /**
     * @inheritdoc
     */
    protected function getConfirmMessage()
    {
        return $this->txt("superior_confirmation_cancel");
    }

    /**
     * @inheritdoc
     */
    protected function getConfirmAlertMessage()
    {
        return $this->txt("superior_confirmation_cancel_alert");
    }

    /**
     * @inheritdoc
     */
    protected function requiredCancelMode()
    {
        require_once(__DIR__ . "/../Settings/class.ilBookingModalitiesGUI.php");
        return \ilBookingModalitiesGUI::SELF_CANCEL;
    }

    /**
     * @inheritdoc
     */
    protected function mightProcessed($usr_id)
    {
        return (int) $this->getActingUser()->getId() == $usr_id;
    }
}
