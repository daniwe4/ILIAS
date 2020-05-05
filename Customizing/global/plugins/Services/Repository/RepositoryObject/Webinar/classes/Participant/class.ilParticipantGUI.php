<?php

use \CaT\Plugins\Webinar;
use CaT\Libs\ExcelWrapper;

/**
 * GUI for member tab
 *
 * @ilCtrl_Calls ilParticipantGUI: ilCSNGUI, ilGenericGUI
 *
 * @author Stefan Hecken
 */
class ilParticipantGUI
{
    const CMD_SHOW_CONTENT = "showContent";
    const CMD_SHOW_PARTICIPANTS = "showParticipants";
    const CMD_CANCEL_PARTICIPANT_CONFIRM = "cancelParticipantConfirm";
    const CMD_CANCEL_PARTICIPANT = "cancelParticipant";
    const CMD_CANCEL_PARTICIPANTS_CONFIRM = "cancelParticipantsConfirm";
    const CMD_CANCEL_PARTICIPANTS = "cancelParticipants";
    const CMD_IMPORT = "importFile";
    const CMD_EXPORT = "exportFileForVC";
    const CMD_DOWNLOAD = "downloadImportedFile";
    const CMD_DELETE = "deleteImportedFile";
    const CMD_ADD_USER_FROM_SEARCH = "addUsersFromSearch";
    const CMD_ADD_USER_FROM_AUTO_COMPLETE = "addUserFromAutoComplete";
    const CMD_SELF_BOOK_USER = "selfBookUser";
    const CMD_BOOK_MEMBER_FROM_COURSE = "bookCourseMember";
    const CMD_MARK_PARTICIPATED = "markParticipated";
    const CMD_UNMARK_PARTICIPATED = "unmarkParticipated";
    const CMD_FINISH = "finish";
    const CMD_CONFIRM_FINISH = "confirmFinish";

    const F_IMPORT_FILE = "importFile";
    const GET_USER_ID = "user_id";
    const GET_USER_TYPE = "user_type";
    const F_CANCEL_ID = "cancel_ids";
    const GET_ID = "id";

    const VC_CSN = "CSN";
    const VC_GENERIC = "Generic";

    private static $FILE_MIME_TYPE = array("application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        "application/excel",
        "application/vnd.ms-excel"
    );
    const FILE_TYPE = "XLSX";

    /**
     * @var Webinar\Settings\ilActions
     */
    protected $actions;

    /**
     * @var Webinar\VC\VCActions
     */
    protected $vc_actions;

    /**
     * @var ilCrtl
     */
    protected $g_ctrl;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $g_tpl;

    /**
     * @var ilObjUser
     */
    protected $g_user;

    /**
     * Writer for xlsx documents
     *
     * @var	ExcelWrapper\Writer
     */
    protected $xlsx_writer;

    /**
     * Writer for csv documents
     *
     * @var	ExcelWrapper\Writer
     */
    protected $csv_writer;

    /**
     * @var Spout\SpoutInterpreter
     */
    protected $interpreter;

    /**
     * @var ilToolbarGUI
     */
    protected $g_toolbar;

    /**
     * @var ilAccessHandler
     */
    protected $g_access;

    public function __construct(
        Webinar\ilActions $actions,
        Webinar\VC\VCActions $vc_actions,
        ExcelWrapper\Writer $xlsx_writer,
        ExcelWrapper\Writer $csv_writer,
        ExcelWrapper\Spout\SpoutInterpreter $interpreter
    ) {
        $this->actions = $actions;
        $this->vc_actions = $vc_actions;
        $this->xlsx_writer = $xlsx_writer;
        $this->csv_writer = $csv_writer;
        $this->interpreter = $interpreter;

        global $DIC;
        $this->g_ctrl = $DIC->ctrl();
        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->g_toolbar = $DIC->toolbar();
        $this->g_user = $DIC->user();
        $this->g_access = $DIC->access();
        $this->g_log = $DIC->logger()->root();
        $this->g_event_handler = $DIC["ilAppEventHandler"];
    }

    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd(self::CMD_SHOW_PARTICIPANTS);
        $next_class = $this->g_ctrl->getNextClass();

        switch ($next_class) {
            case "ilrepositorysearchgui":
                require_once 'Services/Search/classes/class.ilRepositorySearchGUI.php';
                $rep_search = new ilRepositorySearchGUI();
                $rep_search->setCallback($this, self::CMD_ADD_USER_FROM_SEARCH);
                $this->g_ctrl->forwardCommand($rep_search);
                break;
            case "ilcsngui":
                require_once($this->actions->getObject()->getDirectory() . "/classes/VC/CSN/class.ilCSNGUI.php");
                if ($cmd == ilCSNGUI::CMD_APPLY_FILTER
                    || $cmd == ilCSNGUI::CMD_RESET_FILTER
                ) {
                    $this->setToolbar();
                    $gui = new ilCSNGUI($this, $this->vc_actions, $this->actions);
                    $gui->addToolbarItems($this->g_toolbar);
                    $this->g_ctrl->forwardCommand($gui);
                    break;
                }
                //Forward only commands for filtering
                // no break
            case "ilgenericgui":
                require_once($this->actions->getObject()->getDirectory() . "/classes/VC/Generic/class.ilGenericGUI.php");
                if ($cmd == ilGenericGUI::CMD_APPLY_FILTER
                    || $cmd == ilGenericGUI::CMD_RESET_FILTER
                ) {
                    $this->setToolbar();
                    $gui = new ilGenericGUI($this, $this->vc_actions, $this->actions);
                    $gui->addToolbarItems($this->g_toolbar);
                    $this->g_ctrl->forwardCommand($gui);
                    break;
                }
                //Forward only commands for filtering
                // no break
            default:
                switch ($cmd) {
                    case self::CMD_CANCEL_PARTICIPANT_CONFIRM:
                        $this->cancelParticipantConfirm();
                        break;
                    case self::CMD_CANCEL_PARTICIPANTS_CONFIRM:
                        $this->cancelParticipantsConfirm();
                        break;
                    case self::CMD_SHOW_CONTENT:
                    case self::CMD_SHOW_PARTICIPANTS:
                        $this->showParticipants();
                        break;
                    case self::CMD_CANCEL_PARTICIPANT:
                        $this->cancelParticipant();
                        break;
                    case self::CMD_IMPORT:
                        $this->importFile();
                        break;
                    case self::CMD_EXPORT:
                        $this->exportFileForVC();
                        break;
                    case self::CMD_DOWNLOAD:
                        $this->downloadImportedFile();
                        break;
                    case self::CMD_DELETE:
                        $this->deleteImportedFile();
                        break;
                    case self::CMD_ADD_USER_FROM_SEARCH:
                        $this->addUsersFromSearch();
                        break;
                    case self::CMD_ADD_USER_FROM_AUTO_COMPLETE:
                        $this->addUserFromAutoComplete();
                        break;
                    case self::CMD_CANCEL_PARTICIPANTS:
                        $this->cancelParticipants();
                        break;
                    case self::CMD_SELF_BOOK_USER:
                        $this->selfBookUser();
                        break;
                    case self::CMD_BOOK_MEMBER_FROM_COURSE:
                        $this->bookCourseMember();
                        break;
                    case self::CMD_MARK_PARTICIPATED:
                        $this->markParticipated();
                        break;
                    case self::CMD_UNMARK_PARTICIPATED:
                        $this->unmarkParticipated();
                        break;
                    case self::CMD_CONFIRM_FINISH:
                        $this->confirmFinish();
                        break;
                    case self::CMD_FINISH:
                        $this->finish();
                        break;
                    default:
                        throw new Exception(__METHOD__ . " unknown command " . $cmd);
                }
        }
    }

    /**
     * Shows confirmaiton form before finish
     *
     * @return void
     */
    protected function confirmFinish()
    {
        if ($this->logRequired()) {
            \ilUtil::sendInfo($this->txt("list_required"), true);
            $this->g_ctrl->redirect($this, self::CMD_SHOW_PARTICIPANTS);
        }
        require_once "./Services/Utilities/classes/class.ilConfirmationGUI.php";
        $confirmation = new \ilConfirmationGUI();
        $confirmation->setFormAction($this->g_ctrl->getFormAction($this));
        $confirmation->setHeaderText($this->txt("confirm_finish"));
        $confirmation->setCancel($this->txt("cancel"), self::CMD_SHOW_PARTICIPANTS);
        $confirmation->setConfirm($this->txt("finish"), self::CMD_FINISH);

        $this->g_tpl->setContent($confirmation->getHtml());
    }

    /**
     * Checks an log upload is required
     *
     * @return bool
     */
    protected function logRequired()
    {
        $object = $this->actions->getObject();
        $settings = $object->getSettings();
        $file_storage = $object->getFileStorage();
        $vc_type = $settings->getVCType();
        switch ($vc_type) {
            case self::VC_CSN:
                $vc_settings = $this->vc_actions->select();
                return $vc_settings->isUploadRequired() && $file_storage->isEmpty();
                break;
            default:
                return false;
        }

        return false;
    }

    /**
     * Finish the webinar
     *
     * @return void
     */
    protected function finish()
    {
        $fnc = function ($s) {
            return $s->withFinished(true);
        };
        $object = $this->actions->getObject();
        $object->updateSettings($fnc);
        $object->update();
        $this->actions->refreshLP();

        $this->throwFinalizedEvent();
        $this->g_ctrl->redirect($this, self::CMD_SHOW_PARTICIPANTS);
    }

    public function throwFinalizedEvent()
    {
        $object = $this->actions->getObject();
        $parent_course = $object->getParentCourse();
        $payload = [
            "crs_ref_id" => (int) $parent_course->getRefId(),
            "crs_obj_id" => (int) $parent_course->getId()
        ];
        $this->g_event_handler->raise("Plugin/Webinar", "webinar_finalized", $payload);
    }

    /**
     * Show table with all participants
     *
     * @return null
     */
    protected function showParticipants()
    {
        $this->setToolbar();
        $settings = $this->actions->getObject()->getSettings();
        $vc_type = $settings->getVCType();
        switch ($vc_type) {
            case self::VC_CSN:
                require_once($this->actions->getObject()->getDirectory() . "/classes/VC/CSN/class.ilCSNGUI.php");
                $gui = new ilCSNGUI($this, $this->vc_actions, $this->actions);
                $gui->addToolbarItems($this->g_toolbar);
                $this->g_ctrl->forwardCommand($gui);
                break;
            case self::VC_GENERIC:
                require_once($this->actions->getObject()->getDirectory() . "/classes/VC/Generic/class.ilGenericGUI.php");
                $gui = new ilGenericGUI($this, $this->vc_actions, $this->actions);
                $gui->addToolbarItems($this->g_toolbar);
                $this->g_ctrl->forwardCommand($gui);
                break;
            default:
                throw new Exception(__METHOD__ . " type of vc is not knwon " . $vc_type);
        }
    }

    /**
     * Add user from auto complete input
     */
    protected function addUserFromAutoComplete()
    {
        $class = $this->callback['class'];
        $method = $this->callback['method'];

        $users = explode(',', $_POST['user_login']);
        $user_ids = array();
        foreach ($users as $user) {
            $user_id = ilObjUser::_lookupId($user);
            if ($user_id) {
                $user_ids[] = $user_id;
            }
        }

        $user_type = isset($_REQUEST['user_type']) ? $_REQUEST['user_type'] : 0;

        $this->addUsersFromSearch($user_ids);
    }

    public function addUsersFromSearch($user_ids)
    {
        if ($user_ids && is_array($user_ids) && !empty($user_ids)) {
            require_once("Services/User/classes/class.ilObjUser.php");
            $booked = false;
            foreach ($user_ids as $user_id) {
                $user_name = ilObjUser::_lookupLogin($user_id);

                if (!$this->actions->isBookedUser((int) $user_id)) {
                    $this->bookParticipant($user_id, $user_name);
                    $booked = true;
                }
            }

            if ($booked) {
                ilUtil::sendSuccess($this->txt("user_booked"), true);
            } else {
                ilUtil::sendInfo($this->txt("no_booking"), true);
            }
        } else {
            ilUtil::sendInfo($this->txt("search_no_selection"), true);
        }

        $this->g_ctrl->redirect($this, self::CMD_SHOW_PARTICIPANTS);
    }

    /**
     * Book user to webinar
     *
     * @param int 	$user_id
     * @param string 	$user_name
     *
     * @return null
     */
    protected function bookParticipant($user_id, $user_name)
    {
        $this->actions->bookParticipant((int) $user_id, $user_name);
    }

    /**
     * Shows confirmation for for cancel participant
     *
     * @return null
     */
    protected function cancelParticipantConfirm()
    {
        $get = $_GET;

        if ($get[self::GET_USER_TYPE] == "unknown") {
            $delete_id = (int) $get[self::GET_ID];
            $user_type = "unknown";
        } else {
            $delete_id = (int) $get[self::GET_USER_ID];
            $user_type = "known";
        }

        require_once "./Services/Utilities/classes/class.ilConfirmationGUI.php";
        $confirmation = new \ilConfirmationGUI();
        $confirmation->setFormAction($this->g_ctrl->getFormAction($this));
        $confirmation->setHeaderText($this->txt("should_be_deleted"));

        $confirmation->addHiddenItem(self::GET_ID, $delete_id);
        $confirmation->addHiddenItem(self::GET_USER_TYPE, $user_type);

        $confirmation->setCancel($this->txt("cancel"), self::CMD_SHOW_PARTICIPANTS);
        $confirmation->setConfirm($this->txt("delete"), self::CMD_CANCEL_PARTICIPANT);

        $this->g_tpl->setContent($confirmation->getHtml());
    }

    /**
     * Cancel booking of participant
     *
     * @return null
     */
    protected function cancelParticipant()
    {
        $post = $_POST;
        if ($post[self::GET_USER_TYPE] == "unknown") {
            $this->vc_actions->deleteUnknownParticipant((int) $post[self::GET_ID]);
        } else {
            $this->actions->cancelParticipitation((int) $post[self::GET_ID]);
        }

        $this->g_ctrl->redirect($this, self::CMD_SHOW_PARTICIPANTS);
    }

    /**
     * Shows confirmation for for cancel participants
     *
     * @return null
     */
    protected function cancelParticipantsConfirm()
    {
        $post = $_POST;
        $to_delete = array();

        $delete_id = (int) $post[self::GET_ID];
        $user_type = "unknown";

        foreach ($post[self::F_CANCEL_ID] as $key => $id) {
            $delete_id = (int) $id;
            if ($post["hidden_id"][$key] == "") {
                $user_type = "unknown";
            } else {
                $user_type = "known";
            }

            $to_delete[] = array("delete_id" => $delete_id, "user_type" => $user_type);
        }

        require_once "./Services/Utilities/classes/class.ilConfirmationGUI.php";
        $confirmation = new \ilConfirmationGUI();
        $confirmation->setFormAction($this->g_ctrl->getFormAction($this));
        $confirmation->setHeaderText($this->txt("should_be_deleted"));

        $confirmation->addHiddenItem(self::F_CANCEL_ID, base64_encode(serialize($to_delete)));

        $confirmation->setCancel($this->txt("cancel"), self::CMD_SHOW_PARTICIPANTS);
        $confirmation->setConfirm($this->txt("delete"), self::CMD_CANCEL_PARTICIPANTS);

        $this->g_tpl->setContent($confirmation->getHtml());
    }

    /**
     * Cancel booking of participants
     *
     * @return null
     */
    protected function cancelParticipants()
    {
        $post = $_POST;
        $to_delete = unserialize(base64_decode($post[self::F_CANCEL_ID]));

        foreach ($to_delete as $key => $values) {
            $id = $values["delete_id"];
            if ($values["user_type"] == "unknown") {
                $this->vc_actions->deleteUnknownParticipant((int) $id);
            } else {
                $this->actions->cancelParticipitation((int) $id);
            }
        }

        $this->g_ctrl->redirect($this, self::CMD_SHOW_PARTICIPANTS);
    }

    /**
     * Export an XLSX for VC
     *
     * @return null
     */
    protected function exportFileForVC()
    {
        $settings = $this->actions->getObject()->getSettings();
        $vc_type = $settings->getVCType();
        switch ($vc_type) {
            case self::VC_CSN:
                $export = $this->actions->getObject()->getExport($this->csv_writer, $this->interpreter);
                break;
            case self::VC_GENERIC:
                $export = $this->actions->getObject()->getExport($this->xlsx_writer, $this->interpreter);
                break;
        }

        $export->run();
        \ilUtil::deliverFile($export->getFilePath(), $export->getFileName(), '', false, true);
    }

    /**
     * Import file for VC
     *
     * @return null
     */
    protected function importFile()
    {
        $file_info = $_FILES[self::F_IMPORT_FILE];

        if (!$file_info["tmp_name"]) {
            $this->g_ctrl->redirect($this, self::CMD_SHOW_PARTICIPANTS);
        }

        if (!in_array($file_info["type"], self::$FILE_MIME_TYPE)) {
            ilUtil::sendInfo(sprintf($this->txt("wrong_file_type"), self::FILE_TYPE), true);
            $this->g_ctrl->redirect($this, self::CMD_SHOW_PARTICIPANTS);
        }

        $import = $this->actions->getObject()->getImport();
        $file_storage = $this->actions->getObject()->getFileStorage();

        if (!$file_storage->uploadFile($file_info)) {
            \ilUtil::sendFailure("file_could_not_be_uploaded");
            $this->show();
            return;
        }

        try {
            $file_path = $file_storage->getFilePath();
            $content = $import->parseFile($file_path);
        } catch (\Exceptions\InvalidFileException $e) {
            $message = $this->txt(sprintf($this->txt("wrong_file_type"), self::FILE_TYPE));
            $this->cleanupAfterError($message);
            return;
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            $message = $this->txt("parse_error")
                . ' '
                . $e->getMessage();
            $this->cleanupAfterError($message);
            return;
        }

        foreach ($content as $line) {
            $participant = $this->vc_actions->getParticipantByUserName($line["user_name"]);
            if ($participant === null) {
                $this->vc_actions->createUnkownParticipant(
                    $line["user_name"],
                    $line["email"],
                    $line["phone"],
                    $line["company"],
                    (int) $line["minutes"]
                );
            } else {
                $participant = $participant->withMinutes($line["minutes"]);
                $this->vc_actions->updateParticipant($participant);

                $passed = (int) $line["minutes"] >= $this->vc_actions->getMinutesRequired();
                $this->actions->setParticipationStatus($participant->getUserId(), $passed);
            }
        }

        $this->g_ctrl->redirect($this, self::CMD_SHOW_PARTICIPANTS);
    }

    /**
     * When uploading/parsing fails, delete the uploaded file
     * and send appropiate message for user.
     *
     * @param string $message
     * @return void
     */
    protected function cleanupAfterError($message)
    {
        assert('is_string($message)');
        $this->g_log->write('Webinar, error reading file: ' . $message);
        $file_storage = $this->actions->getObject()->getFileStorage();
        $file_storage->deleteCurrentFile();
        \ilUtil::sendFailure($message);
        $this->g_ctrl->setCmd(self::CMD_SHOW_PARTICIPANTS);
        $this->showParticipants();
    }


    /**
     * Download imported files
     *
     * @return null
     */
    protected function downloadImportedFile()
    {
        $file_storage = $this->actions->getObject()->getFileStorage();
        $file_path = $file_storage->getFilePath();

        $filename = basename($file_path);
        ilUtil::deliverFile($file_path, $filename);
    }

    /**
     * Delete imported file and data
     *
     * @return null
     */
    protected function deleteImportedFile()
    {
        $file_storage = $this->actions->getObject()->getFileStorage();
        $file_storage->deleteCurrentFile();
        $this->vc_actions->deleteUnkownParticipants();
        $this->vc_actions->resetMinutesOfBookedUsers();

        foreach ($this->actions->getAllBookedUserIds() as $user_id) {
            $this->actions->setParticipationStatus($user_id, null);
        }

        $this->g_ctrl->redirect($this, self::CMD_SHOW_PARTICIPANTS);
    }

    /**
     * Translate code
     *
     * @param string 	$code
     *
     * @return string
     */
    protected function txt($code)
    {
        return $this->actions->getObject()->pluginTxt($code);
    }

    /**
     * Set the toolbar items
     *
     * @return null
     */
    protected function setToolbar()
    {
        $object = $this->actions->getObject();
        $settings = $object->getSettings();

        $edit_member = $this->g_access->checkAccess("edit_member", "", $object->getRefId());
        $edit_participation = $this->g_access->checkAccess("edit_participation", "", $object->getRefId());

        if ($edit_member
            && !$settings->isFinished()
        ) {
            require_once("Services/Search/classes/class.ilRepositorySearchGUI.php");
            ilRepositorySearchGUI::fillAutoCompleteToolbar(
                $this,
                $this->g_toolbar,
                array(
                    'auto_complete_name' => $this->txt('user'),
                    'submit_name' => $this->txt('add'),
                    'add_search' => true
                )
            );
        }

        $this->g_toolbar->setFormAction($this->g_ctrl->getFormAction($this), true);
    }

    /**
     * User books himself to webinar
     *
     * @return null
     */
    protected function selfBookUser()
    {
        $this->bookParticipant($this->g_user->getId(), $this->g_user->getLogin());
        $link = $this->g_ctrl->getLinkTargetByClass("ilObjWebinarGUI", "", "", false, false);
        \ilUtil::redirect($link);
    }

    /**
     * Book all user from parent course
     *
     * @return null
     */
    protected function bookCourseMember()
    {
        $parent_crs = $this->actions->getObject()->getParentCourse();
        if (is_null($parent_crs)) {
            \ilUtil::sendInfo($this->txt("no_parent_course"), true);
            $this->g_ctrl->redirect($this, self::CMD_SHOW_PARTICIPANTS);
        }

        if ($this->actions->getObject()->getSettings()->isFinished()) {
            \ilUtil::sendInfo($this->txt("webinar_finished"), true);
            $this->g_ctrl->redirect($this, self::CMD_SHOW_PARTICIPANTS);
        }

        $this->importCourseMember($parent_crs);
    }

    /**
     * Imports course member to webinar
     *
     * @param \ilObjCourse 	$parent_crs
     *
     * @return vois
     */
    protected function importCourseMember(\ilObjCourse $parent_crs)
    {
        $course_members = $parent_crs->getMembersObject()->getMembers();
        foreach ($course_members as $user_id) {
            $this->bookOrPortUser((int) $user_id);
        }

        $this->deleteUnkownParticipants();
        $this->deleteNotCourseMember($course_members);

        if (count($course_members) > 0) {
            \ilUtil::sendSuccess($this->txt("crs_user_booked"), true);
        }

        $this->g_ctrl->redirect($this, self::CMD_SHOW_PARTICIPANTS);
    }

    /**
     * Books or ports course member
     *
     * @param int 	$user_id
     *
     * @return void
     */
    protected function bookOrPortUser($user_id)
    {
        $login = \ilObjUser::_lookupLogin((int) $user_id);
        $unknown_user = $this->vc_actions->getUnknownParticipantByLogin($login);
        if (!is_null($unknown_user)) {
            $this->actions->portUserToBookParticipant($user_id, $unknown_user);
            $this->vc_actions->deleteUnknownParticipant($unknown_user->getId());
        } elseif (!$this->actions->isBookedUser((int) $user_id)) {
            $this->bookParticipant((int) $user_id, $login);
        }
    }

    /**
     * Deletes all unknown participants
     *
     * @return void
     */
    protected function deleteUnkownParticipants()
    {
        $this->vc_actions->deleteUnkownParticipants();
    }


    /**
     * Delete all webinar participant not member of course
     *
     * @param int[] 	$course_members
     *
     * @return void
     */
    protected function deleteNotCourseMember(array $course_members)
    {
        $booked_users = $this->actions->getAllBookedUserIds();
        $missing_users = array_diff($booked_users, $course_members);

        foreach ($missing_users as $missing_user) {
            $this->actions->cancelParticipitation((int) $missing_user);
        }
    }

    /**
     * Mark selected user as participated
     *
     * @return null
     */
    protected function markParticipated()
    {
        $selected = $_POST[self::F_CANCEL_ID];
        $mark_ids = $_POST["hidden_id"];

        if ($selected) {
            foreach ($selected as $key => $id) {
                $user_id = $mark_ids[$key];

                if ($user_id !== "") {
                    $user_id = (int) $user_id;
                    $this->actions->setParticipationStatus($user_id, true);
                }
            }
            \ilUtil::sendInfo($this->txt("users_set_participated"), true);
        }
        $this->g_ctrl->redirect($this, self::CMD_SHOW_PARTICIPANTS);
    }

    /**
     * Unark selected user as participated
     *
     * @return null
     */
    protected function unmarkParticipated()
    {
        $selected = $_POST[self::F_CANCEL_ID];
        $mark_ids = $_POST["hidden_id"];

        if ($selected) {
            foreach ($selected as $key => $id) {
                $user_id = $mark_ids[$key];

                if ($user_id !== "") {
                    $user_id = (int) $user_id;
                    $this->actions->setParticipationStatus($user_id, false);
                }
            }
            \ilUtil::sendInfo($this->txt("users_set_not_participated"), true);
        }
        $this->g_ctrl->redirect($this, self::CMD_SHOW_PARTICIPANTS);
    }
}
