<?php

namespace CaT\Plugins\Accomodation\Steps;

use \CaT\Plugins\Accomodation\Reservation\Constants;
use \CaT\Plugins\Accomodation\ilActions;

use \CaT\Ente\Entity;
use CaT\Ente\ILIAS\ilHandlerObjectHelper;

use \ILIAS\TMS\Booking;
use ILIAS\TMS\CourseInfo;
use ILIAS\TMS\CourseInfoHelper;

use ILIAS\UI;

abstract class BookAccomodationsStep
{
    use ilHandlerObjectHelper;
    use CourseInfoHelper;

    const F_ACCOMODATION = "accomodations";
    const NO_ACCOMODATION = "1";
    const NEED_ACCOMODATION = "2";

    /**
     * @var	Entity
     */
    protected $entity;

    /**
     * @var callable
     */
    protected $txt;

    /**
     * @var	ilActions
     */
    protected $actions;

    /**
     * @var \ilObjUser
     */
    protected $acting_user;

    /**
     * @var string
     */
    protected $selected;

    public function __construct(Entity $entity, callable $txt, ilActions $actions, \ilObjUser $acting_user)
    {
        $this->entity = $entity;
        $this->txt = $txt;
        $this->actions = $actions;
        $this->acting_user = $acting_user;
    }

    protected function getEntityRefId()
    {
        return $this->entity()->object()->getRefId();
    }

    protected function getDIC()
    {
        return $GLOBALS["DIC"];
    }

    protected function getUIRenderer()
    {
        return $this->getDIC()->ui()->renderer();
    }

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

    protected function getEventHandler()
    {
        $dic = $this->getDIC();
        return $dic["ilAppEventHandler"];
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
        return $this->txt("default_booking_step_label");
    }

    /**
     * Get a description for this step in the process.
     *
     * @return	string
     */
    public function getDescription()
    {
        return $this->txt("default_booking_step_description");
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
        return 60;
    }

    /**
     * Find out if this step is applicable for the booking process of the
     * given user.
     * First of all, there should be any available overnight accomodation.
     *
     * @param	int	$usr_id
     * @return	bool
     */
    public function isApplicableFor($usr_id)
    {
        if (count($this->actions->getReservationOptionsForUser()) === 0) {
            return false;
        }

        $course = $this->entity->object();
        require_once("Modules/Course/classes/class.ilCourseParticipants.php");
        require_once("Services/Membership/classes/class.ilWaitingList.php");
        if (\ilCourseParticipants::_isParticipant($course->getRefId(), $usr_id)
                || \ilWaitingList::_isOnList($usr_id, $course->getId())
        ) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function appendToStepForm(\ilPropertyFormGUI $form, $usr_id)
    {
        require_once("Services/Form/classes/class.ilCheckboxInputGUI.php");

        $factory = $this->getUIFactory();
        $renderer = $this->getUIRenderer();
        $info = $this->getCourseInfo(CourseInfo::CONTEXT_ACCOMODATION_DEFAULT_INFO);

        $this->appendToForm($factory, $renderer, $info, $form);

        require_once __DIR__ . "/../Reservation/class.ilReservationGUI.php";
        $gui = new \ilReservationGUI(
            $this->actions,
            $this->txt,
            \ilObjectFactory::getInstanceByObjId($usr_id)
        );

        $ne = new \ilNonEditableValueGUI(
            $this->txt('user_reservations'),
            "f_r_label",
            true
        );
        $ne->setInfo($this->txt('user_reservation_byline'));
        $form->addItem($ne);
        $rg = new \ilRadioGroupInputGUI("", "accomodations");
        $rb = new \ilRadioOption($this->txt("no_accomodations"), self::NO_ACCOMODATION);
        $rg->addOption($rb);
        $rb2 = new \ilRadioOption($this->txt("need_accomodations"), self::NEED_ACCOMODATION);
        $gui->addReservationsAsSubItem($rb2);
        $rg->addOption($rb2);
        $form->addItem($rg);

        if ($this->getEditNotes()) {
            $ta = new \ilTextAreaInputGUI($this->txt("notes"), Constants::F_NOTE);
            $ta->setMaxNumOfChars(400);
            $ta->setInfo($this->txt("notes_info"));
            $form->addItem($ta);
        }
    }

    /**
     * Get the data the step needs to store until the end of the process, based
     * on the form.
     *
     * The data needs to be plain PHP data that can be serialized/unserialized
     * via json.
     *
     * @param	\ilPropertyFormGUI	$form
     * @return	array<string, mixed>
     */
    public function getData(\ilPropertyFormGUI $form)
    {
        $ret = [];
        $reservations = [];

        $i_need_accomodations = $form->getInput(self::F_ACCOMODATION);

        if (is_null($i_need_accomodations) || $i_need_accomodations == '') {
            $item = $form->getItemByPostVar(self::F_ACCOMODATION);
            $item->setAlert($this->txt('select_accomodation_wanted'));
            return null;
        }

        if ($i_need_accomodations == self::NEED_ACCOMODATION) {
            $post_values = $form->getInput(Constants::F_USER_RESERVATION);
            if (!$post_values) {
                $item = $form->getItemByPostVar(Constants::F_USER_RESERVATION);
                $item->setAlert($this->txt('select_overnights'));
                $this->selected = $i_need_accomodations;
                return null;
            }

            foreach ($post_values as $value) {
                $ar = explode('--', $value);
                $reservations[] = [
                    'session_id' => (int) $ar[0],
                    'date' => $ar[1],
                    'selfpay' => false,
                ];
            }
        }

        $ret[self::F_ACCOMODATION] = $i_need_accomodations;
        $ret[Constants::F_USER_RESERVATION] = $reservations;

        if ($this->getEditNotes()) {
            $note = "";
            if ($form->getInput(Constants::F_NOTE)) {
                $note = $form->getInput(Constants::F_NOTE);
            }

            $ret[Constants::F_NOTE] = $note;
        }

        return $ret;
    }

    /**
     * @inheritdoc
     */
    public function addDataToForm(\ilPropertyFormGUI $form, $data)
    {
        $values = array();

        if (count($data) > 0) {
            if (array_key_exists(self::F_ACCOMODATION, $data)) {
                if (!is_null($this->selected)) {
                    $values[self::F_ACCOMODATION] = $this->selected;
                } else {
                    $values[self::F_ACCOMODATION] = $data[self::F_ACCOMODATION];
                }
            }

            if ($this->getEditNotes()) {
                if (array_key_exists(Constants::F_NOTE, $data)) {
                    $values[Constants::F_NOTE] = $data[Constants::F_NOTE];
                }
            }

            if (array_key_exists(Constants::F_USER_RESERVATION, $data)) {
                $values[Constants::F_USER_RESERVATION] = array();
                foreach ($data[Constants::F_USER_RESERVATION] as $key => $value) {
                    $values[Constants::F_USER_RESERVATION][] = $value["session_id"] . "--" . $value["date"];
                }
            }
        }

        $form->setValuesByArray($values);
    }

    /**
     * @inheritdoc
     */
    public function appendToOverviewForm($data, \ilPropertyFormGUI $form, $usr_id)
    {
        $factory = $this->getUIFactory();
        $renderer = $this->getUIRenderer();
        $info = $this->getCourseInfo(CourseInfo::CONTEXT_ACCOMODATION_DEFAULT_INFO);
        $this->appendToForm($factory, $renderer, $info, $form);

        $reservations = array($this->txt("booking_step_overview_none"));
        if (
            count($data) > 0 &&
            array_key_exists(Constants::F_USER_RESERVATION, $data) &&
            count($data[Constants::F_USER_RESERVATION]) > 0
        ) {
            $reservations = [];
            $prior_night = $this->actions->getPriorNightDate();
            $post_night = $this->actions->getPostNightDate();
            foreach ($data[Constants::F_USER_RESERVATION] as $reservation) {
                $usr_reservation = $reservation["date"];
                $usr_reservation_string = $this->actions->formatDate($usr_reservation, true);
                $usr_reservation_next_string = $this->getNextDayLabel($usr_reservation);

                if ($usr_reservation === $prior_night) {
                    $reservations[] = $this->txt('priorday') . " - " . $usr_reservation_next_string;
                } elseif ($usr_reservation === $post_night) {
                    $reservations[] = $usr_reservation_string . " - " . $this->txt('postday');
                } else {
                    $reservations[] = $usr_reservation_string . " - " . $usr_reservation_next_string;
                }
            }
        }

        $ne = new \ilNonEditableValueGUI($this->txt("booking_step_overview_label"), '', true);
        $ne->setValue(implode('<br />', $reservations));
        $form->addItem($ne);

        if ($this->getEditNotes()) {
            $ne = new \ilNonEditableValueGUI($this->txt("notes"), '', true);
            $ne->setValue($data[Constants::F_NOTE]);
            $form->addItem($ne);
        }
    }

    protected function getNextDayLabel($dat)
    {
        $ildat = new \ilDateTime($dat, IL_CAL_DATE);
        $ildat_next = clone $ildat;
        $ildat_next->increment(\ilDateTime::DAY, 1);
        $label_next = $this->actions->formatDate($ildat_next, false);
        return $label_next;
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
     * @return	string | null
     */
    public function processStep($crs_ref_id, $usr_id, $data)
    {
        $message = null;
        foreach ($data[Constants::F_USER_RESERVATION] as $reservation) {
            $this->actions->insertReservation(
                $this->actions->getObjId(),
                $usr_id,
                $reservation["session_id"],
                $reservation["date"],
                $reservation["selfpay"]
            );
        }

        $note_db = $this->actions->note_db;

        if ($note_db->nodeExists($this->actions->getObjId(), $usr_id)) {
            $note_db->update(
                $this->actions->getObjId(),
                $usr_id,
                $data[Constants::F_NOTE]
            );
        } else {
            $note_db->createNote(
                $this->actions->getObjId(),
                $usr_id,
                $data[Constants::F_NOTE] ?? ""
            );
        }

        if (count($data) > 0) {
            $message = sprintf($this->txt("booking_accomodations_done"), '');
        }

        $this->actions->raiseReservationUpdateEventFor($usr_id);
        return $message;
    }

    private function getEditNotes() : bool
    {
        $obj_settings = $this->actions->getObjSettings();
        return $obj_settings->getEditNotes();
    }
}
