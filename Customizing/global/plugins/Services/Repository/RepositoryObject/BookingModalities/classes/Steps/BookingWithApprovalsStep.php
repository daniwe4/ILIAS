<?php

namespace CaT\Plugins\BookingModalities\Steps;

use \ILIAS\TMS\Booking;
use \CaT\Ente\Entity;
use CaT\Ente\ILIAS\ilHandlerObjectHelper;

use ILIAS\TMS\CourseInfo;
use ILIAS\TMS\CourseInfoHelper;
use \ILIAS\TMS\MyUsersHelper;

abstract class BookingWithApprovalsStep
{
    use ilHandlerObjectHelper;
    use CourseInfoHelper;
    use MyUsersHelper;

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

    /**
     */
    protected function getUIFactory()
    {
        global $DIC;
        return $DIC->ui()->factory();
    }

    const SUPERIOR_CONFIRMATION_CHECKBOX = "sup_conf_checkbox";
    const MODALITIES_CONFIRMATION_CHECKBOX = "mod_conf_checkbox";
    const ID_OF_BOOKED_USER = "f_usr_id";

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
     * @var	string | null
     */
    protected $modalities_doc;

    /**
     * @var \ilObjBookingModalities
     */
    protected $owner;

    /**
     * @var \ilObjUser
     */
    protected $acting_user;

    /**
     * @var \ilObjUser
     */
    protected $global_user;


    public function __construct(
        Entity $entity,
        callable $txt,
        Booking\Actions $actions,
        \ilObjBookingModalities  $owner,
        \ilObjUser $acting_user,
        \ilObjUser $global_user
    ) {
        $this->entity = $entity;
        $this->txt = $txt;
        $this->booking_actions = $actions;
        $this->owner = $owner;
        $this->acting_user = $acting_user;
        $this->global_user = $global_user;
    }

    /**
     * i18n
     *
     * @param	string	$id
     * @return	string	$text
     */
    protected function txt($id)
    {
        assert('is_string($id)');
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
     * @inheritdoc
     */
    public function appendToStepForm(\ilPropertyFormGUI $form, $usr_id)
    {
        require_once("Services/Form/classes/class.ilCheckboxInputGUI.php");
        require_once("Services/Form/classes/class.ilFormSectionHeaderGUI.php");
        require_once("Services/Form/classes/class.ilHiddenInputGUI.php");

        $factory = $this->getUIFactory();
        $renderer = $this->getUIRenderer();

        $sec = new \ilFormSectionHeaderGUI();
        $sec->setTitle($this->getLabel());
        $form->addItem($sec);

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

        $hi = new \ilHiddenInputGUI(self::ID_OF_BOOKED_USER);
        $hi->setValue($usr_id);
        $form->addItem($hi);
    }

    /**
     * Add the confirmation checkbox to form
     */
    protected function addConfirmCheckBox(\ilPropertyFormGUI $form)
    {
        $online = new \ilCheckboxInputGUI("", self::SUPERIOR_CONFIRMATION_CHECKBOX);
        $online->setInfo($this->getConfirmMessage());
        $form->addItem($online);
    }

    /**
     * Get confirm message for booking step
     *
     * @return true
     */
    abstract protected function getConfirmMessage();

    /**
     * Get confirm message for booking step
     *
     * @return true
     */
    abstract protected function getConfirmAlertMessage();

    /**
     * Get messge to show after success booking
     *
     * @param string 	$crs_title
     * @param int 	$usr_id
     *
     * @return true
     */
    abstract protected function getBookingDoneMessage($crs_title, $usr_id);

    /**
     * Get messge to show after success booking on waitinglist
     *
     * @param string 	$crs_title
     * @param int 	$usr_id
     *
     * @return true
     */
    abstract protected function getBookWaitingDoneMessage($crs_title, $usr_id);

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
        $usr_id = (int) $form->getInput(self::ID_OF_BOOKED_USER);
        $ok = true;
        if (!$form->getInput(self::SUPERIOR_CONFIRMATION_CHECKBOX)
            && !$this->skipConfirmCheck()
        ) {
            $ok = false;
            $item = $form->getItemByPostVar(self::SUPERIOR_CONFIRMATION_CHECKBOX);
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
            return array(self::ID_OF_BOOKED_USER => $usr_id);
        } else {
            \ilUtil::sendFailure($this->txt("confirmation_msg"));
            return null;
        }
    }

    /**
     * Disableds the form check for confirm message
     *
     * @return bool
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
        $values[self::SUPERIOR_CONFIRMATION_CHECKBOX] = true;
        if ($this->modalities_doc) {
            $values[self::MODALITIES_CONFIRMATION_CHECKBOX] = true;
        }

        $values[self::ID_OF_BOOKED_USER] = $data[self::ID_OF_BOOKED_USER];

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
        if ($this->mightProcessed($usr_id)) {
            return $this->txt("no_permissions_to_book_with_approval");
            //throw new \Exception($this->txt("no_permissions_to_book"));
        }

        $crs = \ilObjectFactory::getInstanceByRefId($crs_ref_id);
        assert('$crs instanceof \ilObjCourse');

        $state = $this->booking_actions->bookUser($crs_ref_id, $usr_id);
        if ($state === Booking\Actions::STATE_BOOKED) {
            $message = $this->getBookingDoneMessage($crs->getTitle(), $usr_id);
        } elseif ($state === Booking\Actions::STATE_WAITING_LIST) {
            $message = $this->getBookWaitingDoneMessage($crs->getTitle(), $usr_id);
        } else {
            throw new \LogicException("Unknown State: $state");
        }

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

    protected function getAccess()
    {
        if (is_null($this->access)) {
            $this->access = $this->getDIC()["ilAccess"];
        }

        return $this->access;
    }
}
