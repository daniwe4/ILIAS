<?php

namespace CaT\Plugins\BookingModalities\Steps;

use \ILIAS\TMS\Booking;

class SuperiorHardCancelStep extends HardCancelStep implements Booking\SuperiorBookingStep
{
    /**
     * @inheritdocs
     */
    public function getLabel()
    {
        return $this->txt("hard_cancel_step_label");
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
     * @inheritdoc
     */
    public function getConfirmMessage()
    {
        return $this->txt("employee_confirmation_cancel");
    }

    /**
     * @inheritdoc
     */
    public function getConfirmAlertMessage()
    {
        return $this->txt("employee_confirmation_cancel_alert");
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
    protected function requiredCancelMode()
    {
        require_once(__DIR__ . "/../Settings/class.ilBookingModalitiesGUI.php");
        return \ilBookingModalitiesGUI::SUPERIOR_CANCEL;
    }

    /**
     * @inheritdoc
     */
    protected function getCancelBookingDoneMessage($title, $usr_id)
    {
        $fullname = \ilObjUser::_lookupFullname($usr_id);
        return sprintf($this->txt("superior_booking_hard_cancel_done"), $title, $fullname);
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
