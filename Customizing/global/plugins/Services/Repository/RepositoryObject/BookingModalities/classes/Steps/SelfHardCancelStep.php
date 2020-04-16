<?php

namespace CaT\Plugins\BookingModalities\Steps;

use \ILIAS\TMS\Booking;

class SelfHardCancelStep extends HardCancelStep implements Booking\SelfBookingStep
{
    /**
     * @inheritdoc
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
        return $this->txt("superior_confirmation_cancel");
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
    public function getConfirmAlertMessage()
    {
        return $this->txt("superior_confirmation_cancel_alert");
    }

    /**
     * @inheritdoc
     */
    protected function mightProcessed($usr_id)
    {
        return $this->getActingUser()->getId() == $usr_id;
    }

    /**
     * @inheritdoc
     */
    protected function requiredCancelMode()
    {
        require_once(__DIR__ . "/../Settings/class.ilBookingModalitiesGUI.php");
        return \ilBookingModalitiesGUI::SELF_CANCEL;
    }
}
