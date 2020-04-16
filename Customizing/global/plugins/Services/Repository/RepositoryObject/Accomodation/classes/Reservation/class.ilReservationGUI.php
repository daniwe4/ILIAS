<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> */

declare(strict_types=1);

use \CaT\Plugins\Accomodation\Reservation\CourseInformationFormGUI;
use \CaT\Plugins\Accomodation\Reservation\Constants;
use \CaT\Plugins\Accomodation\ilActions;
use CaT\Plugins\Accomodation\Reservation\Note\Note;

/**
 * GUI for Reservations (user view)
 */
class ilReservationGUI
{
    use CourseInformationFormGUI;

    const CMD_DEFAULT = "showContent";
    const CMD_EDIT = "editReservations";
    const CMD_SAVE = "saveReservations";
    const F_LABEL = 'f_r_label';
    const F_DEADLINE_LABEL = 'f_l_deadline';

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilAccess
     */
    protected $access;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilActions
     */
    protected $actions;

    /**
     * @var Closure
     */
    protected $txt;

    /**
     * User the reservation will be done for
     *
     * @var ilObjUser
     */
    protected $reserved_user;

    public function __construct(ilActions $actions, Closure $txt, ilObjUser $reserved_user)
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->access = $DIC->access();
        $this->app_event_handler = $DIC["ilAppEventHandler"];

        $this->actions = $actions;
        $this->txt = $txt;
        $this->reserved_user = $reserved_user;
    }

    public function txt(string $code) : string
    {
        $txt = $this->txt;

        return $txt($code);
    }


    /**
     * Delegate commands
     *
     * @throws \Exception
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();

        switch ($cmd) {
            case self::CMD_DEFAULT:
            case self::CMD_EDIT:
                $this->editReservations();
                break;
            case self::CMD_SAVE:
                $this->saveReservations();
                break;
            default:
                return;
        }
    }


    protected function mayEdit() : bool
    {
        return (
            $this->access->checkAccessOfUser(
                $this->reserved_user->getId(),
                "book_accomodation",
                "",
                $this->actions->getRefId()
            ) &&
            $this->actions->isInBookingDeadline()
        );
    }


    /**
     * command: show the editing GUI
     */
    protected function editReservations(ilPropertyFormGUI $form = null)
    {
        if ($form === null) {
            $form = $this->initForm();
            $form = $this->initInformationForm($form);
            $form = $this->initReservationForm($form);

            if ($this->getObjSettings()->getEditNotes()) {
                $form = $this->initNotesForm($form);
            }
        }

        if ($this->mayEdit()) {
            $form->addCommandButton(self::CMD_SAVE, $this->txt("xoac_save"));
        }

        $this->tpl->setContent($form->getHtml());
    }

    protected function getObjSettings()
    {
        return $this->actions->getObject()->getObjSettings();
    }

    /**
     * command: store reservations for the current user
     */
    protected function saveReservations()
    {
        $usr_id = (int) $this->reserved_user->getId();
        $post = $_POST;
        $note = $post[Constants::F_NOTE];

        $update_reservations = function ($user_reservations, $reservation_actions) use ($post) {
            $post_values = $post[Constants::F_USER_RESERVATION];

            if (!$post_values) {
                $post_values = array();
            }

            $lookup = array();
            foreach ($user_reservations as $ur) {
                $k = $ur->getAccomodationObjId() . '--' . $ur->getDate()->get(IL_CAL_DATE);
                $lookup[$k] = $ur;
                if (!in_array($k, $post_values)) {
                    $reservation_actions['delete'][] = $ur->getId();
                }
            }

            foreach ($post_values as $value) {
                if (!array_key_exists($value, $lookup)) {
                    $ar = explode('--', $value);
                    $reservation_actions['create'][] = array(
                        'date' => $ar[1],
                        'selfpay' => false
                    );
                }
            }

            return $reservation_actions;
        };

        if (!is_null($note)) {
            $this->actions->updateNote($note, $usr_id);
        }
        $this->actions->updateUserReservations($update_reservations, $usr_id);
        $this->actions->raiseReservationUpdateEventFor($usr_id);

        ilUtil::sendSuccess($this->txt("reservation_successful_saved"), true);
        $this->ctrl->redirect($this, self::CMD_EDIT);
    }


    /**
     * Init a new form
     */
    protected function initForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        return $form;
    }


    /**
     * Init the reservation-part (section/deadline) of the form
     */
    public function initReservationForm(ilPropertyFormGUI $form) : ilPropertyFormGUI
    {
        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->txt('reservation_section_reservations'));
        $form->addItem($section);

        $deadline = $this->actions->getBookingDeadline();
        $ne = new ilNonEditableValueGUI($this->txt('booking_deadline'), self::F_DEADLINE_LABEL);
        if ($deadline) {
            $ne->setValue($this->actions->formatDate($deadline));
        } else {
            ilUtil::sendInfo($this->txt("no_available_reservations"));
        }

        $form->addItem($ne);

        return $this->initReservationOptions($form);
    }

    /**
     * Init the reservation-options of the form
     */
    public function initReservationOptions(ilPropertyFormGUI $form) : ilPropertyFormGUI
    {
        $ne = new ilNonEditableValueGUI($this->txt('user_reservations'), self::F_LABEL, true);
        $ne->setInfo($this->txt('user_reservation_byline'));

        $form->addItem($ne);

        $ms = $this->getReservationMultiSelect();
        $form->addItem($ms);

        return $form;
    }

    public function addReservationsAsSubItem($form_item)
    {
        $ms = $this->getReservationMultiSelect();
        $form_item->addSubItem($ms);
    }

    protected function getReservationMultiSelect()
    {
        $usr_id = (int) $this->reserved_user->getId();

        $obj_id = $this->actions->getObjId();
        $reservation_options = $this->actions->getReservationOptionsForUser();
        $prior_night = $this->actions->getPriorNightDate();
        $post_night = $this->actions->getPostNightDate();
        $reservations = $this->actions->getReservationsForUser($usr_id);

        $options = array();
        foreach ($reservation_options as $dat) {
            if (is_null($dat)) {
                continue;
            }
            $label = $this->actions->formatDate($dat, true);
            $label_next = $this->getNextDayLabel($dat);

            if ($dat === $prior_night) {
                $label = $this->txt('priorday') . ' - ' . $label_next;
            }

            if ($dat === $post_night) {
                $label = $label . ' - ' . $this->txt('postday');
            }

            if ($dat != $prior_night && $dat != $post_night) {
                $label = $label . ' - ' . $label_next;
            }

            $key = (string) $obj_id . '--' . $dat;
            $options[$key] = $label;
        }

        $checked = array();
        foreach ($reservations as $reservation) {
            $dat = new \DateTime($reservation->getDate()->get(IL_CAL_DATE));
            $dat = (string) $obj_id . '--' . $dat->format("Y-m-d");

            if (array_key_exists($dat, $options)) {
                $checked[] = $dat;
            }
        }

        $ms = new ilMultiSelectInputGUI("", Constants::F_USER_RESERVATION);
        $ms->setOptions($options);
        $ms->setValue($checked);
        $ms->setHeight(200);
        $ms->setWidth(250);
        $ms->setDisabled(!$this->mayEdit());

        return $ms;
    }

    protected function getNextDayLabel(string $dat)
    {
        $ildat = new ilDateTime($dat, IL_CAL_DATE);
        $ildat_next = clone $ildat;
        $ildat_next->increment(ilDateTime::DAY, 1);
        $label_next = $this->actions->formatDate($ildat_next, false);

        return $label_next;
    }

    protected function initNotesForm(ilPropertyFormGUI $form) : ilPropertyFormGUI
    {
        $note = $this->getNote();

        $note_value = "";
        if (!is_null($note)) {
            $note_value = $note->getNote();
        }

        $ta = new ilTextAreaInputGUI($this->txt("notes"), Constants::F_NOTE);
        $ta->setMaxNumOfChars(400);
        $ta->setDisabled(!$this->mayEdit());
        $ta->setInfo($this->txt("notes_info"));
        $ta->setValue($note_value);

        $form->addItem($ta);

        return $form;
    }

    protected function getNote()
    {
        $usr_id = (int) $this->reserved_user->getId();
        $obj_id = (int) $this->actions->getObjId();
        $note = $this->actions->getNoteFor($obj_id, $usr_id);

        return $note;
    }
}
