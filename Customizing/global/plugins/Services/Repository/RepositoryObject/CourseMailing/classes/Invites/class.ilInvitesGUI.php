<?php

declare(strict_types=1);

use CaT\Plugins\CourseMailing\Invites;
use CaT\Plugins\CourseMailing\Surroundings\Surroundings;

/**
 * @ilCtrl_Calls ilInvitesGUI: ilRepositorySearchGUI
 */
class ilInvitesGUI extends TMSTableParentGUI
{
    const TABLE_ID = 'invited_usr';

    const F_USR_MULTI_SELECT = "usr_multi_select";
    const F_USR_ID = "usr_id";

    const CMD_VIEW_INVITED = 'viewInvited';
    const CMD_CONFIRM_DELETE_USER = 'confirmDeleteUser';
    const CMD_DELETE_USER = 'deleteUser';
    const CMD_INVITE_USER = 'inviteUser';
    const CMD_INVITE_MAIL = 'inviteMail';
    const CMD_REJECT_USER = 'rejectUser';
    const CMD_ADD_USER_FROM_SEARCH = "addUsersFromSearch";

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $tpl;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var Closure
     */
    protected $txt;

    /**
     * @var Invites\DB
     */
    protected $db;

    /**
     * @var Surroundings
     */
    protected $surroundings;

    /**
     * @var string
     */
    protected $plugin_dir;

    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var ilObjUser
     */
    protected $current_user;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    public function __construct(
        ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
        ilToolbarGUI $toolbar,
        Closure $txt,
        Invites\DB $db,
        Surroundings $surroundings,
        string $plugin_dir,
        ilObjUser $current_user,
        ilAccessHandler $access
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->toolbar = $toolbar;
        $this->txt = $txt;
        $this->db = $db;
        $this->surroundings = $surroundings;
        $this->plugin_dir = $plugin_dir;
        $this->current_user = $current_user;
        $this->access = $access;
    }

    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass();

        switch ($next_class) {
            case "ilrepositorysearchgui":
                $rep_search = new ilRepositorySearchGUI();
                $rep_search->setCallback($this, self::CMD_ADD_USER_FROM_SEARCH);
                $this->ctrl->setReturn($this, self::CMD_VIEW_INVITED);
                $this->ctrl->forwardCommand($rep_search);
                break;
            default:
                $cmd = $this->ctrl->getCmd();

                switch ($cmd) {
                    case self::CMD_VIEW_INVITED:
                        $this->viewInvited();
                        break;
                    case self::CMD_CONFIRM_DELETE_USER:
                        $this->confirmDeleteUser();
                        break;
                    case self::CMD_DELETE_USER:
                        $this->deleteUser();
                        break;
                    case self::CMD_INVITE_USER:
                        $this->inviteUser();
                        break;
                    case self::CMD_REJECT_USER:
                        $this->rejectUser();
                        break;
                    case self::CMD_ADD_USER_FROM_SEARCH:
                        $this->addUsersFromSearch([]);
                        break;
                    case self::CMD_INVITE_MAIL:
                        $this->inviteMail();
                        break;
                    default:
                        throw new Exception('Unknown command: ' . $cmd);
                }
        }
    }

    protected function viewInvited()
    {
        $this->setToolbar();
        $table = $this->getTable();
        $table->determineOffsetAndOrder();
        $order_column = $table->getOrderField();
        $order_direction = $table->getOrderDirection();
        $selected_columns = $table->getSelectedColumns();
        $offset = $table->getOffset();
        $limit = $table->getLimit();

        $data = $this->db->getInvitedUserFor(
            $this->getObjId(),
            $order_column,
            $order_direction,
            $offset,
            $limit,
            $selected_columns
        );
        $cnt_data = $this->db->countInvitedUserFor($this->getObjId());
        $table->setData($data);
        $table->setMaxCount($cnt_data);
        $this->tpl->setContent($table->getHTML());
    }

    protected function confirmDeleteUser()
    {
        $to_delete = $_POST[self::F_USR_MULTI_SELECT];
        $user_ids = $_POST[self::F_USR_ID];

        if (is_null($to_delete) || count($to_delete) == 0) {
            ilUtil::sendInfo($this->txt('no_user_selected'), true);
            $this->ctrl->redirect($this, self::CMD_VIEW_INVITED);
        }

        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ctrl->getFormAction($this));
        $confirm->setHeaderText($this->txt('sure_delete_user'));

        foreach ($to_delete as $key => $id) {
            $user_id = $user_ids[$id];
            $info = ilObjUser::_lookupName($user_id);
            $text = $info['lastname'] . ', ' . $info['firstname'] . ' (' . $info['login'] . ')';
            $confirm->addItem(self::F_USR_MULTI_SELECT . "[]", $id, $text);
        }

        $confirm->setConfirm("Löschen", self::CMD_DELETE_USER);
        $confirm->setCancel("Abbrechen", self::CMD_VIEW_INVITED);

        $this->tpl->setContent($confirm->getHTML());
    }

    protected function deleteUser()
    {
        $to_delete = $_POST[self::F_USR_MULTI_SELECT];

        if (is_null($to_delete) || count($to_delete) == 0) {
            ilUtil::sendInfo($this->txt('no_user_selected'), true);
            $this->ctrl->redirect($this, self::CMD_VIEW_INVITED);
        }

        foreach ($to_delete as $id) {
            $this->db->deleteUser((int) $id);
        }
        ilUtil::sendSuccess($this->txt('user_deleted'), true);
        $this->ctrl->redirect($this, self::CMD_VIEW_INVITED);
    }

    protected function inviteUser()
    {
        $to_delete = $_POST[self::F_USR_MULTI_SELECT];

        if (is_null($to_delete) || count($to_delete) == 0) {
            ilUtil::sendInfo($this->txt('no_user_selected'), true);
            $this->ctrl->redirect($this, self::CMD_VIEW_INVITED);
        }

        foreach ($to_delete as $id) {
            $this->db->setInvitedByUser((int) $id, $this->getCurrentUserId());
        }
        ilUtil::sendSuccess($this->txt('user_marked_as_invited'), true);
        $this->ctrl->redirect($this, self::CMD_VIEW_INVITED);
    }

    protected function rejectUser()
    {
        $to_delete = $_POST[self::F_USR_MULTI_SELECT];

        if (is_null($to_delete) || count($to_delete) == 0) {
            ilUtil::sendInfo($this->txt('no_user_selected'), true);
            $this->ctrl->redirect($this, self::CMD_VIEW_INVITED);
        }

        foreach ($to_delete as $id) {
            $this->db->setRejectedByUser((int) $id, $this->getCurrentUserId());
        }
        ilUtil::sendSuccess($this->txt('user_marked_as_rejected'), true);
        $this->ctrl->redirect($this, self::CMD_VIEW_INVITED);
    }

    protected function getTable()
    {
        $table = $this->getTMSTableGUI();
        $table->setFormAction($this->ctrl->getFormAction($this));
        $table->setRowTemplate("tpl.invited_row.html", $this->plugin_dir);
        $table->setDefaultOrderField("lastname");
        $table->setShowRowsSelector(true);

        $table->addColumn("", "", "1", true);
        $table->addColumn($this->txt("lastname"), "lastname");
        $table->addColumn($this->txt("firstname"), "firstname");

        $columns = $table->getSelectedColumns();
        if (count($columns) > 0) {
            $selectables = $table->getSelectableColumns();

            foreach ($columns as $column) {
                if (array_key_exists($column, $selectables)) {
                    $table->addColumn($selectables[$column]['txt']);
                }
            }
        }

        $table->addColumn($this->txt("invite"));
        $table->addColumn($this->txt("details_invite"));
        $table->addColumn($this->txt("crs_part_status"));
        $table->addColumn($this->txt("rejected"));
        $table->addColumn($this->txt("details_rejected"));

        if ($this->canEdit()) {
            $table->setSelectAllCheckbox(self::F_USR_MULTI_SELECT);
            $table->addMultiCommand(self::CMD_INVITE_MAIL, $this->txt("send_invite_mail"));
            $table->addMultiCommand(self::CMD_INVITE_USER, $this->txt("set_invited"));
            $table->addMultiCommand(self::CMD_REJECT_USER, $this->txt("set_rejected"));
            $table->addMultiCommand(self::CMD_CONFIRM_DELETE_USER, $this->txt("delete_usr"));
        }

        return $table;
    }

    public function addUsersFromSearch($user_ids)
    {
        $show_existing_message = false;
        if ($user_ids && is_array($user_ids) && !empty($user_ids)) {
            foreach ($user_ids as $user_id) {
                if ($this->db->isAdded((int) $user_id, $this->getObjId())) {
                    $show_existing_message = true;
                    continue;
                }

                if ($this->db->isAdded((int) $user_id, $this->getObjId(), true)) {
                    $this->db->reactivateUser(
                        (int) $user_id,
                        $this->getObjId(),
                        $this->getCurrentUserId()
                    );
                    continue;
                }

                $this->db->addUser((int) $user_id, $this->getObjId(), $this->getCurrentUserId());
            }
        } else {
            ilUtil::sendInfo($this->txt("no_user_selected"), true);
        }

        if ($show_existing_message) {
            ilUtil::sendInfo($this->txt("user_still_added"), true);
        }
        $this->ctrl->redirect($this, self::CMD_VIEW_INVITED);
    }

    public function inviteMail()
    {
        $to_delete = $_POST[self::F_USR_MULTI_SELECT];
        if (is_null($to_delete) || count($to_delete) == 0) {
            ilUtil::sendInfo($this->txt('no_user_selected'), true);
            $this->ctrl->redirect($this, self::CMD_VIEW_INVITED);
        }

        $rcps = [];
        foreach ($to_delete as $id) {
            $rcps[] = $this->db->getLoginById((int) $id);
        }

        if (!count(array_filter($rcps))) {
            ilUtil::sendInfo($this->txt('no_user_selected'), true);
            $this->ctrl->redirect($this, self::CMD_VIEW_INVITED);
        }

        ilMailFormCall::setRecipients($rcps);

        require_once  "Customizing/global/plugins/Services/Repository/RepositoryObject/CourseMailing/classes/Invites/class.ilCourseMailingInviteContext.php";
        $link = ilMailFormCall::getRedirectTarget(
            $this,
            self::CMD_VIEW_INVITED,
            [],
            [
                'type' => 'new'
            ],
            [
                ilMailFormCall::CONTEXT_KEY => ilCourseMailingInviteContext::ID,
                'ref_id' => $this->getRefId(),
                'obj_id' => $this->getObjId(),
                'usr_id' => $this->getCurrentUserId(),
                'crs_ref_id' => $this->surroundings->getParentCourseRefId(),
                'crs_id' => $this->surroundings->getParentCourseId(),
                'ts' => time()
            ]
        );
        $this->ctrl->redirectToURL($link);
    }

    protected function setToolbar()
    {
        if ($this->canEdit()) {
            ilRepositorySearchGUI::fillAutoCompleteToolbar(
                $this,
                $this->toolbar,
                array(
                    'auto_complete_name' => $this->txt('user'),
                    'submit_name' => $this->txt('add'),
                    'add_search' => true
                )
            );
        }
    }

    protected function fillRow()
    {
        return function (ilTMSTableGUI $table, Invites\Invite $set) {
            $tpl = $table->getTemplate();

            if ($this->canEdit()) {
                $tpl->setCurrentBlock('checkb');
                $tpl->setVariable('POST_VAR', self::F_USR_MULTI_SELECT);
                $tpl->setVariable('ID', $set->getId());
                $tpl->setVariable('POST_USR', self::F_USR_ID);
                $tpl->setVariable('POST_USR_KEY', $set->getId());
                $tpl->setVariable('USR_ID', $set->getUsrId());
                $tpl->parseCurrentBlock();
            }

            $tpl->setVariable('LASTNAME', $set->getLastname());
            $tpl->setVariable('FIRSTNAME', $set->getFirstname());

            $invite = "-";
            $invite_details = "-";
            if (!is_null($set->getInviteBy())) {
                $usr_infos = $this->getOutputInfosForUser($set->getInviteBy());
                $invite = $this->getFormatedDateTime($set->getInviteAt());
                $invite_details = join(', ', $usr_infos);
            }
            $tpl->setVariable('INVITE', $invite);
            $tpl->setVariable('DETAILS_INVITE', $invite_details);

            $tpl->setVariable('CRS_PART_STATUS', $this->getCourseMemberStatus($set->getUsrId()));

            $rejected = "-";
            $rejected_details = "-";
            if (!is_null($set->getRejectedBy())) {
                $usr_infos = $this->getOutputInfosForUser($set->getRejectedBy());
                $rejected = $this->getFormatedDateTime($set->getRejectedAt());
                $rejected_details = join(', ', $usr_infos);
            }
            $tpl->setVariable('REJECTED', $rejected);
            $tpl->setVariable('DETAILS_REJECTED', $rejected_details);

            foreach ($set->getUdfFields() as $val) {
                $tpl->setCurrentBlock('udf_col');
                $tpl->setVariable('VALUE', $val);
                $tpl->parseCurrentBlock();
            }
        };
    }

    protected function tableCommand()
    {
        return self::CMD_VIEW_INVITED;
    }

    protected function tableId()
    {
        return self::TABLE_ID;
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }

    public function getSelectableColumns()
    {
        include_once './Services/PrivacySecurity/classes/class.ilExportFieldsInfo.php';

        $ef = ilExportFieldsInfo::_getInstanceByType('crs');
        $all_columns = $ef->getSelectableFieldsInfo($this->surroundings->getParentCourseId());

        unset($all_columns['login']);
        return $all_columns;
    }

    protected function getCourseMemberStatus(int $usr_id) : string
    {
        $roles = $this->surroundings->getRolesForMember($usr_id);
        $on_waiting_list = $this->surroundings->isOnWaitingList($usr_id);

        if ($on_waiting_list) {
            return $this->txt('waiting');
        }

        if (count($roles) == 0) {
            return "-";
        }

        return join(
            ', ',
            array_map(
                function ($role) {
                    if (substr($role, 0, 3) === 'il_') {
                        return $this->txt($role);
                    }
                    return $role;
                },
                $roles
            )
        );
    }

    protected function getObjId() : int
    {
        if (is_null($this->obj_id)) {
            $this->obj_id = ilObject::_lookupObjectId($this->getRefId());
        }
        return $this->obj_id;
    }

    protected function getRefId() : int
    {
        return (int) $_GET['ref_id'];
    }

    protected function getCurrentUserId() : int
    {
        return (int) $this->current_user->getId();
    }

    protected function getOutputInfosForUser(int $usr_id) : array
    {
        $infos = ilObjUser::_lookupFields($usr_id);
        return [
            $infos['firstname'] . ' ' . $infos['lastname'],
            $infos['email']
        ];
    }
    protected function getFormatedDateTime(DateTime $date) : string
    {
        $il_date = new ilDateTime($date->format('Y-m-d H:i:s'), IL_CAL_DATETIME);
        return ilDatePresentation::formatDate($il_date);
    }

    protected function canEdit() : bool
    {
        return $this->access->checkAccess("edit_invites", "", $this->getRefId());
    }
}
