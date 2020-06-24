<?php

namespace CaT\Plugins\BookingModalities\Steps;

use \ILIAS\TMS\Booking;
use \CaT\Ente\Entity;
use CaT\Ente\ILIAS\ilHandlerObjectHelper;

use ILIAS\TMS\CourseInfo;
use ILIAS\TMS\CourseInfoHelper;
use ILIAS\UI;

abstract class HardCancelStep
{
    use ilHandlerObjectHelper;
    use CourseInfoHelper;
    use \ILIAS\TMS\MyUsersHelper;

    protected function getDIC()
    {
        return $GLOBALS["DIC"];
    }

    protected function getEntityRefId()
    {
        return $this->entity()->object()->getRefId();
    }

    protected function getUIRenderer()
    {
        return $this->getDIC()->ui()->renderer();
    }

    /**
     */
    protected function getUIFactory()
    {
        global $DIC;
        return $DIC->ui()->factory();
    }

    protected function getActingUser()
    {
        return $this->acting_user;
    }

    public function withActingUser(int $usr_id)
    {
        $clone = clone $this;
        $clone->acting_user = new \ilObjUser($usr_id);
        return $clone;
    }

    const CANCEL_CONFIRMATION_CHECKBOX = "cancel_conf_checkbox";
    const MODALITIES_CONFIRMATION_CHECKBOX = "mod_conf_checkbox";
    const ID_OF_CANCEL_USER = "cancel_user_id";

    /**
     * @var	Entity
     */
    protected $entity;

    /**
     * @var callable
     */
    protected $txt;

    /**
     * @var	Booking\Actions
     */
    protected $booking_actions;

    /**
     * @var	\ilObjUser
     */
    protected $acting_user;


    public function __construct(
        Entity $entity,
        callable $txt,
        Booking\Actions $actions,
        \ilObjBookingModalities $booking_modalities,
        \ilObjUser $acting_user,
        $modalities_doc
    ) {
        $this->entity = $entity;
        $this->txt = $txt;
        $this->booking_actions = $actions;
        $this->owner = $booking_modalities;
        $this->acting_user = $acting_user;
        $this->modalities_doc = $modalities_doc;
    }

    /**
     * i18n
     *
     * @param	string	$id
     * @return	string	$text
     */
    protected function txt(string $id)
    {
        return call_user_func($this->txt, $id);
    }

    /**
     * @inheritdocs
     */
    public function entity()
    {
        return $this->entity;
    }

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
        $can_cancel = false;
        if ($crs_start !== null) {
            $can_cancel = $this->canCancel($crs_start);
        }

        return $can_cancel
                && (
                    $is_booked
                    || $is_waiting
                    );
    }

    /**
     * @inheritdoc
     */
    public function appendToStepForm(\ilPropertyFormGUI $form, $usr_id)
    {
        // quick and dirty to get the warining to prominent place
        $message = $this->txt("hard_cancel_info");

        $course = $this->entity->object();
        $cancellation_fee = $this->getCancellationFee((int) $course->getId(), (int) $usr_id);

        if ($cancellation_fee > 0) {
            $message .= " " . sprintf(
                $this->txt("hard_cancel_costs"),
                number_format($cancellation_fee, "2", ",", ".")
            );
        }

        \ilUtil::sendFailure($message);

        $form->setTitle("Stornierung außerhalb Stornierungszeitraum");

        $factory = $this->getUIFactory();
        $renderer = $this->getUIRenderer();

        $info = $this->getCourseInfo(CourseInfo::CONTEXT_BOOKING_DEFAULT_INFO);
        $this->appendToForm($factory, $renderer, $info, $form);

        $this->addConfirmCheckBox($form);

        if ($this->modalities_doc) {
            $confirm = new \ilCheckboxInputGUI('', self::MODALITIES_CONFIRMATION_CHECKBOX);
            $confirm->setInfo(
                sprintf(
                    $this->txt('modalities_confirmation_txt'),
                    $this->modalities_doc
                )
            );
            $form->addItem($confirm);
        }

        $hi = new \ilHiddenInputGUI(self::ID_OF_CANCEL_USER);
        $hi->setValue($usr_id);
        $form->addItem($hi);
    }

    /**
     * Add the confirmation checkbox to form
     */
    protected function addConfirmCheckBox(\ilPropertyFormGUI $form)
    {
        $online = new \ilCheckboxInputGUI("", self::CANCEL_CONFIRMATION_CHECKBOX);
        $online->setInfo($this->getConfirmMessage());
        $form->addItem($online);
    }

    /**
     * Get the data the step needs to store until the end of the process, based
     * on the form.
     *
     * The data needs to be plain PHP data that can be serialized/unserialized
     * via json.
     *
     * If null is returned, the form was not displayed correctly and needs to
     *
     * @param	\ilPropertyFormGUI	$form
     * @return	int[]|null
     */
    public function getData(\ilPropertyFormGUI $form)
    {
        $usr_id = (int) $form->getInput(self::ID_OF_CANCEL_USER);
        $ok = true;
        if (!$form->getInput(self::CANCEL_CONFIRMATION_CHECKBOX)
            && !$this->skipConfirmCheck()
        ) {
            $ok = false;
            $item = $form->getItemByPostVar(self::CANCEL_CONFIRMATION_CHECKBOX);
            $item->setAlert($this->getConfirmAlertMessage());
        }

        if ($this->modalities_doc) {
            if (!$form->getInput(self::MODALITIES_CONFIRMATION_CHECKBOX)) {
                $ok = false;
                $item = $form->getItemByPostVar(self::MODALITIES_CONFIRMATION_CHECKBOX);
                $item->setAlert($this->txt("modalities_confirmation_alert"));
            }
        }

        if ($ok) {
            return array(self::ID_OF_CANCEL_USER => $usr_id);
        } else {
            \ilUtil::sendFailure($this->txt("confirmation_msg"));
            return null;
        }
    }

    /**
     * @inheritdoc
     */
    protected function skipConfirmCheck()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function addDataToForm(\ilPropertyFormGUI $form, $data)
    {
        $values = array();
        $values[self::ID_OF_CANCEL_USER] = $data[self::ID_OF_CANCEL_USER];
        $form->setValuesByArray($values);
    }

    /**
     * @inheritdoc
     */
    public function appendToOverviewForm($data, \ilPropertyFormGUI $form, $usr_id)
    {
        $factory = $this->getUIFactory();
        $renderer = $this->getUIRenderer();

        $info = $this->getCourseInfo(CourseInfo::CONTEXT_BOOKING_DEFAULT_INFO);
        $this->appendToForm($factory, $renderer, $info, $form);
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
    public function processStep($crs_ref_id, $usr_id, $data)
    {
        if (!$this->mightProcessed($usr_id)) {
            return $this->txt("no_permissions_to_cancel");
        }

        $crs = \ilObjectFactory::getInstanceByRefId($crs_ref_id);
        assert('$crs instanceof \ilObjCourse');

        $state = $this->booking_actions->cancelUser($crs_ref_id, $usr_id);
        if ($state === Booking\Actions::STATE_REMOVED_FROM_COURSE) {
            $message = $this->getCancelBookingDoneMessage($crs->getTitle(), $usr_id);
        } elseif ($state === Booking\Actions::STATE_REMOVED_FROM_WAITINGLIST) {
            $message = $this->getCancelWaitingDoneMessage($crs->getTitle(), $usr_id);
        } /*else {
            throw new \LogicException("Unknown State: $state");
        }*/

        return $message;
    }

    /**
     * Check the step might be processed
     *
     * @param int 	$usr_id
     *
     * @return bool
     */
    abstract protected function mightProcessed($usr_id);

    /**
     * Get the modus is required to be cancel enabled
     *
     * @return string
     */
    abstract protected function requiredCancelMode();

    /**
     * Checks the cancel deadline is not passed
     *
     * @param \ilDateTime 	$crs_start
     *
     * @return bool
     */
    protected function canCancel(\ilDateTime $crs_start)
    {
        $storno_end_date = clone $crs_start;
        $storno_deadline = $this->owner->getStorno()->getDeadline();
        $storno_hard_deadline = $this->owner->getStorno()->getHardDeadline();
        $storno_modus = $this->owner->getStorno()->getModus();

        if ($storno_deadline !== null && $storno_deadline > 0) {
            require_once("Services/Calendar/classes/class.ilDateTime.php");
            $storno_end_date->increment(\ilDateTime::DAY, -1 * $storno_deadline);
        }

        $hard_storno_end_date = clone $crs_start;
        if ($storno_hard_deadline !== null && $storno_hard_deadline > 0) {
            require_once("Services/Calendar/classes/class.ilDateTime.php");
            $hard_storno_end_date->increment(\ilDateTime::DAY, -1 * $storno_hard_deadline);
        }

        $today = date("Y-m-d");
        $cancel_passed = $storno_modus !== null
            && $storno_modus == $this->requiredCancelMode()
            && ($crs_start === null || $today > $storno_end_date->get(IL_CAL_DATE));

        if ($cancel_passed
            && (
                $storno_hard_deadline == 0
                || ($storno_modus !== null && ($crs_start === null || $today <= $hard_storno_end_date->get(IL_CAL_DATE)))
                )
            ) {
            return true;
        }

        return false;
    }

    protected function getAccess()
    {
        if (is_null($this->access)) {
            $this->access = $this->getDIC()["ilAccess"];
        }

        return $this->access;
    }

    protected function getCancellationFee(int $crs_id, int $usr_id) : float
    {
        if (!\ilPluginAdmin::isPluginActive("xacc")) {
            return 0;
        }

        $pl = \ilPluginAdmin::getPluginObjectById("xacc");
        $cancellation_fee = $pl->getCancellationFeeFor($crs_id, $usr_id);

        if (is_null($cancellation_fee)) {
            return 0;
        }

        return $cancellation_fee;
    }

    /**
     * Get the message user has cancelled course for self or employee
     *
     * @param string 	$title
     * @param int 	$usr_id
     *
     * @return string
     */
    protected function getCancelBookingDoneMessage($title, $usr_id)
    {
        return sprintf($this->txt("booking_cancel_done"), $title);
    }

    /**
     * Get the message user has cancelled waitinglist for self or employee
     *
     * @param string 	$title
     * @param int 	$usr_id
     *
     * @return string
     */
    protected function getCancelWaitingDoneMessage($title, $usr_id)
    {
        return sprintf($this->txt("booking_cancel_waiting_list_done"), $title);
    }
}
