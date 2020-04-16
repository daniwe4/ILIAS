<?php

namespace CaT\Plugins\BookingModalities\Steps;

use \ILIAS\TMS\Booking;
use \CaT\Ente\Entity;

class SelfBookingWithApprovalsStep extends BookingWithApprovalsStep implements Booking\SelfBookingWithApprovalsStep
{
    public function __construct(
        Entity $entity,
        callable $txt,
        Booking\Actions $actions,
        \ilObjBookingModalities  $owner,
        \ilObjUser $acting_user,
        \ilObjUser $global_user
    ) {
        parent::__construct($entity, $txt, $actions, $owner, $acting_user, $global_user);
        $course = $this->entity->object();
        $this->modalities_doc = $owner->getActions()->getModalitiesDocForUser((int) $this->global_user->getId());
    }

    /**
     * @inheritdocs
     */
    public function getLabel()
    {
        return $this->txt("default_booking_step_label");
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
        return 20;
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

        $is_participant = \ilCourseParticipants::_isParticipant($course->getRefId(), $usr_id);
        $is_on_waitinglist = \ilWaitingList::_isOnList($usr_id, $course->getId());

        $crs_member = $course->getMembersObject()->getCountMembers();
        $max_member = $this->owner->getMember()->getMax();

        if ($max_member === null) {
            $bookable = true;
        } else {
            $bookable = $crs_member < $max_member;
        }

        return !$is_participant &&
            !$is_on_waitinglist &&
            $bookable &&
            $this->owner->isSelfBooking()
        ;
    }

    /**
     * @inheritdoc
     */
    protected function addConfirmCheckBox(\ilPropertyFormGUI $form)
    {
        if (!$this->owner->getBooking()->getHideSuperiorApprove()) {
            $online = new \ilCheckboxInputGUI("", self::SUPERIOR_CONFIRMATION_CHECKBOX);
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
        return $this->txt("superior_confirmation");
    }

    /**
     * @inheritdoc
     */
    protected function getConfirmAlertMessage()
    {
        return $this->txt("superior_confirmation_alert");
    }

    /**
     * @inheritdoc
     */
    protected function getBookingDoneMessage($crs_title, $usr_id)
    {
        return sprintf($this->txt("booking_booked_done"), $crs_title);
    }

    /**
     * @inheritdoc
     */
    protected function getBookWaitingDoneMessage($crs_title, $usr_id)
    {
        return sprintf($this->txt("booking_waiting_done"), $crs_title);
    }

    /**
     * @inheritdoc
     */
    protected function mightProcessed($usr_id)
    {
        return (int) $this->getActingUser()->getId() !== $usr_id;
    }
}
