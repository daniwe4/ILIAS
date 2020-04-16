<?php

namespace CaT\Plugins\BookingModalities\Steps;

use \ILIAS\TMS\Booking;
use \CaT\Ente\Entity;

class SuperiorBookingWithApprovalsStep extends BookingWithApprovalsStep implements Booking\SuperiorBookingWithApprovalsStep
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
            $this->owner->isSuperiorBooking()
        ;
    }

    /**
     * @inheritdoc
     */
    protected function getConfirmMessage()
    {
        return $this->txt("employee_confirmation");
    }

    /**
     * @inheritdoc
     */
    protected function getConfirmAlertMessage()
    {
        return $this->txt("employee_confirmation_alert");
    }

    /**
     * @inheritdoc
     */
    protected function getBookingDoneMessage($crs_title, $usr_id)
    {
        $fullname = \ilObjUser::_lookupFullname($usr_id);
        return sprintf($this->txt("employee_booking_booked_done"), $fullname, $crs_title);
    }

    /**
     * @inheritdoc
     */
    protected function getBookWaitingDoneMessage($crs_title, $usr_id)
    {
        $fullname = \ilObjUser::_lookupFullname($usr_id);
        return sprintf($this->txt("employee_booking_waiting_done"), $fullname, $crs_title);
    }

    /**
     * Process the data to perform the actions in the system that are required
     * for the step.
     *
     * The data must be the same as the component return via getData.
     *
     * @param	int     $crs_ref_id
     * @param	int     $usr_id
     * @param	mixed $data
     * @return	void
     */
    protected function mightProcessed($usr_id)
    {
        return (int) $this->getActingUser()->getId() !== $usr_id && !$this->checkIsSuperiorEmployeeBelowCurrent($usr_id);
    }

    /**
     * Checks the user course should be boooked to is employee or superior under current user
     *
     * @param int 	$usr_id
     *
     * @return bool
     */
    protected function checkIsSuperiorEmployeeBelowCurrent($usr_id)
    {
        $members_below = $this->getUserWhereCurrentCanBookFor((int) $this->getActingUser()->getId());
        return array_key_exists($usr_id, $members_below);
    }
}
