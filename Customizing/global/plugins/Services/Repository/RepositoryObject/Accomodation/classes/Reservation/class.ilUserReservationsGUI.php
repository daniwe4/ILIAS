<?php
use \CaT\Plugins\Accomodation;
use \CaT\Plugins\Accomodation\ilActions;
use CaT\Plugins\Accomodation\Reservation\Note\Note;
use \CaT\Plugins\Accomodation\Reservation\Reservation;
use \CaT\Plugins\Accomodation\TableProcessing\TableProcessor;
use \CaT\Plugins\Accomodation\Reservation\ilUserReservationsTableGUI;
use \CaT\Plugins\Accomodation\Reservation\ilUserReservationsSingleTableGUI;
use \CaT\Plugins\Accomodation\Reservation\Constants;

require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/Accomodation/classes/CheckboxInput/class.ilNoLabelCheckboxInputGUI.php');


/**
 * GUI for list of reservations
 *
 * @author Nils Haagen	<nils.haagen@concepts-and-training.de>
 */
class ilUserReservationsGUI
{
    const CMD_EDIT = "editUserReservationList";
    const CMD_SEARCH_USER = "searchUserAutoCompletion";
    const CMD_ADD_USER = "addUserReservation";
    const CMD_CONFIRM_DELETE = "confirmUserReservationDelete";
    const CMD_DELETE = "deleteUserReservations";
    const CMD_EDIT_USER = "editUserReservations";
    const CMD_SAVE_USER = "saveUserReservations";
    const CMD_EXPORT_LIST = "exportUserReservations";

    const FILE_NAME_SUFFIX = "xlsx";

    const F_USER_INFO = 'f_user_info';
    const F_HIDDEN_USER_ID = 'f_user_id';
    const F_RES_TABLE_ADD_USER = 'f_new_user';

    const COLUMN_LASTNAME = "lastname";
    const COLUMN_FIRSTNAME = "firstname";
    const COLUMN_LOGIN = "login";
    const COLUMN_ROLE = "role";
    const COLUMN_MAIL = "mail";
    const COLUMN_PHONE = "phone";
    const COLUMN_RESERVATIONS = "reervations";
    const COLUMN_NOTE = "note";

    /**
     * @var \ilObjAccomodationGUI
     */
    protected $parent_gui;

    /**
     * @var \ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var \ilTemplate
     */
    protected $g_tpl;

    /**
     * @var ilActions
     */
    protected $actions;

    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * @var TableProcessor
     */
    protected $table_processor;

    /**
     * @var \ilToolbarGUI
     */
    protected $g_toolbar;

    public function __construct(
        \ilObjAccomodationGUI $parent_gui,
        ilActions $actions,
        \Closure $txt,
        TableProcessor $table_processor
    ) {
        global $DIC;
        $this->g_ctrl = $DIC->ctrl();
        $this->g_toolbar = $DIC->toolbar();
        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->parent_gui = $parent_gui;
        $this->actions = $actions;
        $this->table_processor = $table_processor;
        $this->txt = $txt;
    }

    /**
     * Delegate commands
     *
     * @throws \Exception
     */
    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd();
        switch ($cmd) {
            case self::CMD_EDIT:
                $this->editUserList();
                break;

            case self::CMD_SEARCH_USER:
                $this->searchUserAutoCompletion();
                break;

            case self::CMD_CONFIRM_DELETE:
                $this->confirmDelete();
                break;

            case self::CMD_DELETE:
                $this->delete();
                break;

            case self::CMD_EDIT_USER:
                $this->editSingleUser();
                break;

            case self::CMD_SAVE_USER:
                $this->saveSingleUser();
                break;

            case self::CMD_ADD_USER:
                $this->addUser();
                break;

            case self::CMD_EXPORT_LIST:
                $this->exportUserReservations();
                break;

            default:
                throw new \Exception(__METHOD__ . ": unkown command " . $cmd);
        }
    }

    /**
     * command: show the list of user-reservations
     *
     * @return void
     */
    protected function editUserList()
    {
        $user_reservations = $this->actions->getAllUserReservationsAtObj(true); //include waitinglist

        $data = $this->createProcessingArray($user_reservations);
        $this->renderReservationsTable($data);
    }

    /**
     * command: edit reservations for a single user
     *
     * @return void
     */
    protected function editSingleUser()
    {
        $usr_id = (int) $_GET[Constants::F_RES_TABLE_GETVAR_USR_ID];
        $user_reservations = $this->actions->getReservationsForUser($usr_id);
        $this->renderUserReservationsForm($user_reservations, $usr_id);
    }

    /**
     * Render the list of users and their reservations
     *
     * @param array<int,Reservation[]> 	$reservations
     * @return void
     */
    protected function renderReservationsTable(array $reservations)
    {
        $this->setToolbar();

        //$this->sortReservations($reservations);
        $edit_user_link = $this->g_ctrl->getLinkTarget($this, self::CMD_EDIT_USER);
        $table = new ilUserReservationsTableGUI(
            $this,
            $this->txt,
            $this->actions,
            $edit_user_link,
            self::CMD_EDIT
        );
        $table->determineOffsetAndOrder();
        $order_field = $table->getOrderColumn();
        $order_direction = $table->getOrderDirection();

        $rows = $this->getRowsFromReservations($reservations);

        $table->setData($rows);
        $table->addMulticommand(self::CMD_CONFIRM_DELETE, $this->txt("delete_user_reservations"));

        $this->g_tpl->setContent($table->getHtml());
    }

    protected function getRowsFromReservations(array $reservations)
    {
        $ret = array();

        foreach ($reservations as $key => $reservation) {
            $object = $reservation["object"];
            $row = array();

            $usr_id = $object->getUserId();
            $user = new \ilObjuser($usr_id);
            $note = $object->getNote();

            $note_value = "";
            if (!is_null($note)) {
                $note_value = $note->getNote();
            }
            $row["USER_ID"] = $usr_id;
            $row[self::COLUMN_LASTNAME] = $user->getLastname();
            $row[self::COLUMN_FIRSTNAME] = $user->getFirstname();
            $row[self::COLUMN_LOGIN] = $user->getLogin();
            $row[self::COLUMN_ROLE] = $this->getUserRoles($usr_id);
            $row[self::COLUMN_MAIL] = $user->getEmail();
            $row[self::COLUMN_PHONE] = $user->getPhoneOffice();
            $row[self::COLUMN_RESERVATIONS] = $this->getReservationsForOutput($object->getReservations());
            $row[self::COLUMN_NOTE] = $note_value;

            $ret[] = $row;
        }

        return $ret;
    }

    protected function getReservationsForOutput($user_reservations)
    {
        $this_obj_id = $this->actions->getObjId();
        $res = array();

        $prior_night = $this->actions->getPriorNightDate();
        $post_night = $this->actions->getPostNightDate();

        foreach ($user_reservations as $user_reservation) {
            $r_oac_id = $user_reservation->getAccomodationObjId();

            $dat = $user_reservation->getDate()->get(IL_CAL_DATE);
            $label = $this->actions->formatDate($dat, true);
            $label_next = $this->actions->getNextDayLabel($dat);

            if ($dat === $prior_night) {
                $label = $this->txt('priorday') . ' - ' . $label_next;
            }
            if ($dat === $post_night) {
                $label = $label . ' - ' . $this->txt('postday');
            }
            if ($dat != $prior_night && $dat != $post_night) {
                $label = $label . ' - ' . $label_next;
            }

            if ($r_oac_id !== $this_obj_id) {
                $label .= ' (';
                if ($user_reservation->getSelfpay()) {
                    $label .= $this->txt('table_user_edit_is_selfpay') . ' / ';
                }
                $label .= \ilObject::_lookupTitle($r_oac_id) . ')';
            } else {
                if ($user_reservation->getSelfpay()) {
                    $label .= ' (' . $this->txt('table_user_edit_is_selfpay') . ')';
                }
            }

            $ut = $user_reservation->getDate()->get(IL_CAL_UNIX);
            $res[$ut] = $label;
        }
        ksort($res);
        return array_values($res);
    }

    /**
     * @param 	int	$usr_id
     * @return	string
     */
    private function getUserRoles($usr_id)
    {
        $roles = $this->actions->getCourseRolesOfUser($usr_id);
        return implode('<br>', $roles);
    }

    /**
     * Set form action and elemnts to toolbar
     *
     * @return null
     */
    protected function setToolbar()
    {
        include_once "Services/Form/classes/class.ilTextInputGUI.php";

        $uf = new ilTextInputGUI($this->txt('search_user'), self::F_RES_TABLE_ADD_USER);
        $uf->setDataSource($this->g_ctrl->getLinkTarget($this, self::CMD_SEARCH_USER, "", true));
        $uf->setSubmitFormOnEnter(false);
        $uf->setParent($this->g_toolbar);
        $uf->readFromSession();

        $this->g_toolbar->setFormAction($this->g_ctrl->getFormAction($this));
        $this->g_toolbar->setCloseFormTag(true);
        $this->g_toolbar->addInputItem($uf);
        $this->g_toolbar->addFormButton($this->txt("add_user_reservation"), self::CMD_ADD_USER);

        $this->g_toolbar->addSeparator();
        $this->g_toolbar->addFormButton($this->txt("export_list"), self::CMD_EXPORT_LIST);
    }

    /**
     *
     * @param Reservation[] 	$reservations
     * @param int 	$usr_id
     * @return void
     */
    protected function renderUserReservationsForm(array $reservations, $usr_id)
    {
        assert('is_int($usr_id)');
        require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        require_once("Services/Form/classes/class.ilMultiSelectInputGUI.php");

        $timetable = $this->actions->getSessionsTimeTable();
        $location = $this->actions->getLocation();

        $form = new \ilPropertyFormGUI();
        $form->setFormAction($this->g_ctrl->getFormAction($this));

        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->txt('reservation_section_course_info'));
        $form->addItem($section);

        $user = new \ilObjUser($usr_id);
        $user_template = $new_tpl = new ilTemplate("tpl.user_info.html", true, true, $this->actions->getPluginDirectory());
        $user_template->setVariable("USER_NAME", $user->getFullName());
        $user_template->setVariable("USER_LOGIN", $user->getLogin());
        $user_template->setVariable("USER_MAIL", $user->getEmail());
        $user_template->setVariable("USER_PHONE", $user->getPhoneOffice());
        $user_info = $user_template->get();
        $ne = new ilNonEditableValueGUI($this->txt('user_info'), self::F_USER_INFO, true);
        $ne->setValue($user_info);
        $form->addItem($ne);

        $ne = new ilNonEditableValueGUI($this->txt('course_timetable'), Constants::F_COURSE_TIMETABLE, true);
        $ne->setValue(implode('<br>', $timetable));
        $form->addItem($ne);

        $ne = new ilNonEditableValueGUI($this->txt('accomodation_location'), Constants::F_ACCOMODATION_LOCATION, true);
        if (is_null($location)) {
            \ilUtil::sendInfo($this->txt("no_location_configured"));
        } else {
            $ne->setValue((string) $location->getHTML());
        }

        $form->addItem($ne);

        $suser_table = new ilUserReservationsSingleTableGUI(
            $this,
            $this->txt,
            $this->actions
        );

        $suser_table->addHiddenInput(self::F_HIDDEN_USER_ID, $usr_id);

        $reservation_options = $this->actions->getFullReservationOptions();
        $prior_night = $this->actions->getPriorNightDate();
        $post_night = $this->actions->getPostNightDate();
        $reservations = $this->actions->getReservationsForUser($usr_id);

        $booked = array();
        $selfpay = array();
        foreach ($reservations as $reservation) {
            $reservation_obj_id = $reservation->getAccomodationObjId();
            $dat = new \DateTime($reservation->getDate()->get(IL_CAL_DATE));
            $dat = (string) $reservation_obj_id . '--' . $dat->format("Y-m-d");

            $booked[] = $dat;
            if ($reservation->getSelfpay()) {
                $selfpay[] = $dat;
            }
        }

        $rdata = array();
        $obj_id = (string) $this->actions->getObjId();
        foreach ($reservation_options as $dat) {
            $label = $this->actions->formatDate($dat, true);
            $label_next = $this->actions->getNextDayLabel($dat);

            if ($dat === $prior_night) {
                $label = $this->txt('priorday') . ' - ' . $label_next;
            }
            if ($dat === $post_night) {
                $label = $label . ' - ' . $this->txt('postday');
            }
            if ($dat != $prior_night && $dat != $post_night) {
                $label = $label . ' - ' . $label_next;
            }

            $key = $obj_id . '--' . $dat;
            $rdata[] = array(
                'label' => $label,
                'booked' => in_array($key, $booked),
                'book_disabled' => false,
                'book_value' => $key,
                'selfpay' => in_array($key, $selfpay),
                'selfpay_disabled' => false
            );
        }

        $data = $this->createProcessingArray($rdata);
        $suser_table->setData($data);
        $suser_table->setFormAction($this->g_ctrl->getFormAction($this));
        $suser_table->setCloseFormTag(false);

        $note_form = new \ilPropertyFormGUI();
        $note_form->setOpenTag(false);

        $note = $this->getNote($usr_id);

        $note_value = "";
        if (!is_null($note)) {
            $note_value = $note->getNote();
        }

        $ta = new ilTextAreaInputGUI($this->txt("notes"), Constants::F_NOTE);
        $ta->setMaxNumOfChars(400);
        $ta->setInfo($this->txt("notes_info"));
        $ta->setValue($note_value);

        $note_form->addItem($ta);
        $note_form->addCommandButton(self::CMD_SAVE_USER, $this->txt("xoac_save"));
        $note_form->addCommandButton(self::CMD_EDIT, $this->txt("xoac_cancel"));

        $this->g_tpl->setContent($form->getHtml() . $suser_table->getHTML() . $note_form->getHTML());
    }

    protected function getNote(int $usr_id)
    {
        $obj_id = (int) $this->actions->getObjId();
        $note = $this->actions->getNoteFor($obj_id, $usr_id);

        return $note;
    }

    /**
     * command:  Show confirm form before deletion
     *
     * @return null
     */
    protected function confirmDelete()
    {
        $objects_to_delete = $this->getObjectsFromPost();

        if (count($objects_to_delete)) {
            require_once "./Services/Utilities/classes/class.ilConfirmationGUI.php";
            $confirmation = new \ilConfirmationGUI();
            $confirmation->setFormAction($this->g_ctrl->getFormAction($this));
            $confirmation->setHeaderText($this->txt("confirm_delete_user_reservations"));
            $confirmation->setCancel($this->txt("cancel"), self::CMD_EDIT);
            $confirmation->setConfirm($this->txt("delete"), self::CMD_DELETE);

            foreach ($objects_to_delete as $key => $delete_object) {
                $user = new \ilObjUser($delete_object->getUserId());
                $item = sprintf(
                    '%s (%s) - %s',
                    $user->getFullname(),
                    $user->getLogin(),
                    $delete_object->getDate()->get(IL_CAL_DATE)
                );
                $confirmation->addItem($key, $key, $item);
            }

            $confirmation->addHiddenItem("processing_objects", base64_encode(serialize($objects_to_delete)));
            $this->g_tpl->setContent($confirmation->getHTML());
        } else {
            $this->editUserList();
        }
    }

    /**
     * command: delete reservations
     *
     * @return null
     */
    protected function delete()
    {
        $objects = unserialize(base64_decode($_POST['processing_objects']));
        $objects = $this->createProcessingArray($objects);

        $processing_objects = array();
        foreach ($objects as $entry) {
            $entry['delete'] = true;
            $processing_objects[] = $entry;
        }

        $worked_processing_objects = $this->table_processor->process($processing_objects, array(TableProcessor::ACTION_DELETE));

        foreach ($objects as $entry) {
            $object = $entry["object"];
            $this->actions->raiseReservationUpdateEventFor($object->getUserId());
        }
        \ilUtil::sendInfo($this->txt("reservations_successfully_deleted"));
        $this->editUserList();
    }

    /**
     * command: save reservations for a single user
     *
     * @return void
     */
    protected function saveSingleUser()
    {
        $post = $_POST;
        $usr_id = (int) $post[self::F_HIDDEN_USER_ID];
        $note = $post[Constants::F_NOTE];

        $update_reservations = function ($user_reservations, $reservation_actions) use ($post) {
            $post_values = $post[Constants::F_USER_RESERVATION];
            $post_selfpay = $post[Constants::F_USER_SELFPAY];

            if (!$post_values) {
                $post_values = array();
            }
            if (!$post_selfpay) {
                $post_selfpay = array();
            }

            $lookup = array();
            foreach ($user_reservations as $ur) {
                $k = $ur->getSessionObjId() . '--' . $ur->getDate()->get(IL_CAL_DATE);
                $lookup[$k] = $ur;
                if (!in_array($k, $post_values)) {
                    $reservation_actions['delete'][] = $ur->getId();
                }
            }

            foreach ($post_values as $value) {
                if (!array_key_exists($value, $lookup)) {
                    $ar = explode('--', $value);
                    $reservation_actions['create'][] = array(
                        'session_id' => (int) $ar[0],
                        'date' => $ar[1],
                        'selfpay' => in_array($value, $post_selfpay)
                    );
                } else {
                    if ($lookup[$value]->getSelfpay() !== in_array($value, $post_selfpay)) {
                        $reservation_actions['update'][] = $lookup[$value]->withSelfpay(in_array($value, $post_selfpay));
                    }
                }
            }

            return $reservation_actions;
        };

        $this->actions->updateUserReservations($update_reservations, $usr_id);
        $this->actions->updateNote($note, $usr_id);
        $this->actions->raiseReservationUpdateEventFor($usr_id);
        $this->editUserList();
    }

    /**
     * command: add a user
     *
     * @return void
     */
    protected function addUser()
    {
        $post = $_POST;
        $user_login = $post[self::F_RES_TABLE_ADD_USER];
        $usr_id = \ilObjUser::getUserIdByLogin($user_login);

        if ($usr_id === 0 || in_array($usr_id, $this->actions->getParentCourseMembers()) === false) {
            \ilUtil::sendInfo($this->txt("no_such_member"));
            $this->editUserList();
        } else {
            $user_reservations = $this->actions->getReservationsForUser($usr_id);
            $this->renderUserReservationsForm($user_reservations, $usr_id);
        }
    }

    /**
     * Show auto complete results
     * @return string
     */
    protected function searchUserAutoCompletion()
    {
        $search = $_REQUEST['term'];
        $all = $this->actions->getParentCourseMembers();
        $ret = array(
            "hasMoreResults" => false,
            "items" => array()
        );
        foreach ($all as $usr_id) {
            $usr = new \ilObjuser($usr_id, false);

            $entry = array(
                "value" => $usr->getLogin(),
                "label" =>
                    $usr->getLastname()
                    . ', '
                    . $usr->getFirstname()
                    . ' ['
                    . $usr->getLogin()
                    . '] ,'
                    . $usr->getEmail(),
                "id" => $usr_id
            );
            if (strpos(strtolower($entry['label']), strtolower($search)) !== false) {
                $ret['items'][] = $entry;
            }
        }
        include_once 'Services/JSON/classes/class.ilJsonUtil.php';
        echo \ilJsonUtil::encode($ret);
        exit();
    }

    /**
     * Export the signature list as PDF
     *
     * @return void
     */
    protected function exportUserReservations()
    {
        $export = $this->actions->getObject()->getPDFExporter();
        $export->writeOutput();
        \ilUtil::deliverFile($export->getFilePath(), $export->getFileName(), 'application/pdf', false, true, true);
    }

    /**
     * Create an array of entries for processing this table
     *
     * @param array<int, Reservation[]>
     *
     * @return mixed[]
     */
    protected function createProcessingArray(array $objects)
    {
        $ret = array();
        foreach ($objects as $usr_id => $object) {
            $ret[] = array("object" => $object, "delete" => false, "errors" => array(), "message" => array());
        }

        return $ret;
    }

    /**
     * Get objects whose rows were marked.
     *
     * @return Reservation[]
     */
    protected function getObjectsFromPost()
    {
        $ret = array();
        $post = $_POST;
        $ids = $post[Constants::F_RES_TABLE_POSTVAR_ROW_IDS];
        if ($ids && is_array($ids)) {
            foreach ($ids as $id) {
                $ret = array_merge($ret, $this->actions->getReservationsForUser((int) $id));
            }
        }
        return $ret;
    }

    /**
     * @param 	string	$code
     * @return	string
     */
    public function txt($code)
    {
        assert('is_string($code)');
        $txt = $this->txt;
        return $txt($code);
    }
}
