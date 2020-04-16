<?php

declare(strict_types=1);

namespace CaT\Plugins\BookingApprovals\Approvals;

require_once "Services/TMS/Table/TMSTableParentGUI.php";

use CaT\Plugins\BookingApprovals\ilObjectActions;
use CaT\Plugins\BookingApprovals\Utils\CourseUtils;
use CaT\Plugins\BookingApprovals\Utils\OrguUtils;
use CaT\Plugins\BookingApprovals\Utils\CourseInformation;
use CaT\Plugins\BookingApprovals\Utils\IliasWrapper;

/**
 * Abstract base class for approval guis.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-trainings.de>
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
abstract class ApprovalGUI extends \TMSTableParentGUI
{
    const S_LASTNAME = "lastname";
    const S_FIRSTNAME = "firstname";
    const S_EMAIL = "email";
    const S_CRS_TITLE = "course_title";
    const S_CRS_DATE = "course_date";
    const S_CRS_TYPE = "course_type";
    const S_CRS_PROVIDER = "course_provider";
    const S_CRS_VENUE = "course_venue";
    const S_CRS_CONTENT = "course_content";
    const S_CRS_GOALS = "course_goals";
    const S_CRS_PARTICIPANT_FEE = "course_participant_fee";
    const S_CRS_IDD_TIME_UNITS = "course_idd_time_units";
    const S_APPROVALS = "approvals";
    const S_ACTIONS = "actions";

    const SORT_LASTNAME = "Lastname";
    const SORT_FIRSTNAME = "Firstname";
    const SORT_EMAIL = "Email";
    const SORT_CRS_TITLE = "CourseTitle";
    const SORT_CRS_DATE = "CourseDate";
    const SORT_CRS_TYPE = "CourseType";
    const SORT_CRS_PROVIDER = "CourseProvider";
    const SORT_CRS_VENUE = "CourseVenue";
    const SORT_CRS_PARTICIPANT_FEE = "CourseParticipantFee";
    const SORT_CRS_IDD_TIME_UNITS = "CourseIddTimeUnits";

    const DEFAULT_ORDER_DIRECTION = "asc";

    const CMD_SHOW_OVERVIEW = "showContent";
    const CMD_SHOW_MY_APPROVALS = "showMyApprovals";
    const CMD_SHOW_FINISHED_APPROVALS = "showFinishedApprovals";
    const CMD_SHOW_BLOCKED_APPROVALS = "showBlockedApprovals";
    const CMD_SHOW_SUMMARY = "showSummary";

    const CMD_APPROVE = "approve";
    const CMD_APPROVE_CONFIRMED = "approve_confirmed";

    const CMD_DECLINE = "decline";
    const CMD_DECLINE_CONFIRMED = "decline_confirmed";

    const CMD_REVOKE = "revoke";
    const CMD_REVOKE_CONFIRMED = "revoke_confirmed";

    const CMD_MULTI_ACTION = "multiAction";

    const TABLE_APPROVALS_ID = "approvals";
    const P_EDU_TRACKING = "xetr";

    /**
     * @var ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var ilTemplate
     */
    protected $g_tpl;

    /**
     * @var ilAccessHandler
     */
    protected $g_access;

    /**
     * @var ilObjBookingApprovalsGUI
     */
    protected $parent;

    /**
     * @var CourseUtils
     */
    protected $course_utils;

    /**
     * @var OrguUtils
     */
    protected $orgu_utils;

    /**
     * @var ilObjectActions
     */
    protected $actions;

    /**
     * @var Actions
     */
    protected $approval_actions;

    /**
     * @var \Closure
     */
    protected $txt;

    public function __construct(
        \ilObjBookingApprovalsGUI $parent,
        CourseUtils $course_utils,
        OrguUtils $orgu_utils,
        IliasWrapper $ilias_wrapper,
        ilObjectActions $actions,
        Actions $approval_actions,
        \Closure $txt
    ) {
        global $DIC;

        $this->g_ctrl = $DIC["ilCtrl"];
        $this->g_tpl = $DIC["tpl"];
        $this->g_usr = $DIC["ilUser"];
        $this->g_access = $DIC["ilAccess"];

        $this->parent = $parent;
        $this->course_utils = $course_utils;
        $this->orgu_utils = $orgu_utils;
        $this->ilias_wrapper = $ilias_wrapper;
        $this->actions = $actions;
        $this->approval_actions = $approval_actions;
        $this->txt = $txt;
    }

    /**
     * @throws Exception
     */
    protected function convertStateToString(int $state) : string
    {
        switch ($state) {
            case Approval::OPEN:
                return $this->txt("status_open");
                break;
            case Approval::APPROVED:
                return $this->txt("status_approved");
                break;
            case Approval::DECLINED:
                return $this->txt("status_declined");
                break;
            case Approval::CANCELED_BY_USER:
                return $this->txt("status_revoked_by_user");
                break;
            case Approval::NO_NEXT_APPROVER:
                return $this->txt("status_no_next_approver");
                break;
            default:
                throw new Exception("Unknown state " . $state);
                break;
        }
    }

    public function txt(string $code) : string
    {
        $txt = $this->txt;
        return $txt($code);
    }

    /**
     * Get all additional columns that could be selected.
     */
    public function getSelectableColumns() : array
    {
        $edu = array();
        $default = array();

        if ($this->ilias_wrapper->isPluginActive(self::P_EDU_TRACKING)) {
            $edu = array(self::S_CRS_IDD_TIME_UNITS => array(
                "txt" => $this->txt(self::S_CRS_IDD_TIME_UNITS),
                "sort" => self::SORT_CRS_IDD_TIME_UNITS
            ));
        }

        $default = array(
            self::S_CRS_PROVIDER => array("txt" => $this->txt(self::S_CRS_PROVIDER), "sort" => self::SORT_CRS_PROVIDER),
            self::S_CRS_VENUE => array("txt" => $this->txt(self::S_CRS_VENUE), "sort" => self::SORT_CRS_VENUE),
            self::S_CRS_CONTENT => array("txt" => $this->txt(self::S_CRS_CONTENT), "sort" => ""),
            self::S_CRS_GOALS => array("txt" => $this->txt(self::S_CRS_GOALS), "sort" => ""),
            self::S_CRS_PARTICIPANT_FEE => array("txt" => $this->txt(self::S_CRS_PARTICIPANT_FEE), "sort" => self::SORT_CRS_PARTICIPANT_FEE)
        );

        return array_merge($default, $edu);
    }

    /**
     * Initialize a table.
     */
    protected function getTable() : \ilTMSTableGUI
    {
        require_once("Services/TMS/Table/ilTMSTableGUI.php");

        $table = $this->getTMSTableGUI();

        $table->setRowTemplate($this->getTemplateName(), $this->actions->getObject()->getDirectory());
        $table->setTitle($this->getTableTitle());
        $table->setExternalSegmentation(true);
        $table->setDefaultOrderField(self::SORT_LASTNAME);
        $table->setDefaultOrderDirection(self::DEFAULT_ORDER_DIRECTION);
        $table->setFormAction($this->g_ctrl->getFormAction($this));
        $table->determineOffsetAndOrder();
        $table->determineLimit();

        if ($this->hasApproverActions() || $this->hasRequesterActions()) {
            $table->addColumn("", "", "1", true);
            $table->setSelectAllCheckbox("booking_request_ids");
        }
        if ($this->hasApproverActions()) {
            $table->addMultiCommand(self::CMD_APPROVE, $this->txt(self::CMD_APPROVE));
            $table->addMultiCommand(self::CMD_DECLINE, $this->txt(self::CMD_DECLINE));
        }
        if ($this->hasRequesterActions()) {
            $table->addMultiCommand(self::CMD_REVOKE, $this->txt(self::CMD_REVOKE));
        }

        return $table;
    }

    protected function addTableStandardRows(\ilTMSTableGUI $table) : \ilTMSTableGUI
    {
        $table->addColumn($this->txt(self::S_LASTNAME), self::SORT_LASTNAME);
        $table->addColumn($this->txt(self::S_FIRSTNAME), self::SORT_FIRSTNAME);
        $table->addColumn($this->txt(self::S_EMAIL), self::SORT_EMAIL);
        $table->addColumn($this->txt(self::S_CRS_TITLE), self::SORT_CRS_TITLE);
        $table->addColumn($this->txt(self::S_CRS_DATE), self::SORT_CRS_DATE);
        $table->addColumn($this->txt(self::S_CRS_TYPE), self::SORT_CRS_TYPE);
        $columns = $table->getSelectedColumns();
        if (count($columns) > 0) {
            $selectables = $table->getSelectableColumns();
            foreach ($columns as $column) {
                if (array_key_exists($column, $selectables)) {
                    $col = $selectables[$column];
                    $table->addColumn($col['txt'], $col["sort"]);
                }
            }
        }
        $table->addColumn($this->txt(self::S_APPROVALS), "");
        return $table;
    }

    /**
     * @inheritdoc
     */
    protected function fillRow()
    {
        return function ($table, $set) {
            $tpl = $table->getTemplate();
            $tpl->setVariable("LASTNAME", $set->getLastname());
            $tpl->setVariable("FIRSTNAME", $set->getFirstname());
            $tpl->setVariable("EMAIL", $set->getEmail());
            $tpl->setVariable("CRS_TITLE", $set->getCourseTitle());
            $tpl->setVariable("CRS_DATE", $set->getCourseDate());
            $tpl->setVariable("CRS_TYPE", $set->getCourseType());

            foreach ($set->getApprovals() as $approval) {
                $dat = "";
                $approval_date = $approval->getApprovalDate();
                if ($approval_date != null) {
                    $dat = date_format($approval_date, "d.m.Y H:i");
                }

                $tpl->setCurrentBlock("approvals");
                $tpl->setVariable("POSITION", $this->orgu_utils->getPositionTitleById($approval->getApprovalPosition()));
                $tpl->setVariable("STATE", $this->convertStateToString($approval->getState()));
                $user_id = $approval->getApprovingUserId();
                if ($user_id != null) {
                    $tpl->setVariable(
                        "APPROVAL_DETAILS",
                        sprintf("(%s %s)", \ilObjUser::_lookupFullname($user_id), $dat)
                    );
                }
                $tpl->parseCurrentBlock();
            }

            foreach ($table->getSelectedColumns() as $field) {
                $tpl->setCurrentBlock('custom_fields');

                switch ($field) {
                    case self::S_CRS_PROVIDER:
                        $value = $set->getCourseProvider();
                        break;
                    case self::S_CRS_VENUE:
                        $value = $set->getCourseVenue();
                        break;
                    case self::S_CRS_CONTENT:
                        $value = $set->getCourseContent();
                        break;
                    case self::S_CRS_GOALS:
                        $value = $set->getCourseGoals();
                        break;
                    case self::S_CRS_PARTICIPANT_FEE:
                        $value = $set->getCourseParticipantFee();
                        break;
                    case self::S_CRS_IDD_TIME_UNITS:
                        $value = $set->getCourseIddTimeUnits();
                        break;
                    default:
                        throw new Exception("Unknown column " . $field . ".");
                        break;
                }

                $tpl->setVariable('VAL_CUST', $value);
                $tpl->parseCurrentBlock();
            }

            if ($this->hasRequesterActions()) {
                $tpl->setVariable("ROW_SELECTION_POSTVAR", "booking_request_ids");
                $tpl->setVariable("BOOKING_REQUEST_ID", $set->getBookingRequestId());

                $actions_menu = $this->getActionMenu(
                    $set->getBookingRequestId(),
                    $set->getCourseRefId()
                );
                $tpl->setVariable("ACTIONS", $actions_menu);
            }
        };
    }

    protected function sortData(array $data, string $order_field, string $order_direction)
    {
        $func = "get" . $order_field;

        usort($data, function ($a, $b) use ($func, $order_direction) {
            switch ($order_direction) {
                case "asc":
                    return strcasecmp($a->$func(), $b->$func());
                    break;
                case "desc":
                    return strcasecmp($b->$func(), $a->$func());
                    break;
            }
        });

        return $data;
    }

    /**
     * @return Approval | null
     */
    protected function getNextOpenApproval(array $approvals)
    {
        foreach ($approvals as $approval) {
            if ($approval->isOpen()) {
                return $approval;
            }
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    protected function tableCommand() : string
    {
        return $this->command;
    }

    protected function getTemplateName() : string
    {
        return static::TEMPLATE_NAME;
    }

    protected function getTableTitle() : string
    {
        return $this->txt(static::TABLE_TITLE);
    }

    public function hasApproverActions() : bool
    {
        return static::APPROVER_ACTIONS;
    }

    public function hasRequesterActions() : bool
    {
        return static::REQUESTER_ACTIONS;
    }

    protected function getActionMenu(int $id, int $crs_ref_id) : string
    {
        include_once("Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
        $current_selection_list = new \ilAdvancedSelectionListGUI();
        $current_selection_list->setAsynch(false);
        $current_selection_list->setAsynchUrl(true);
        $current_selection_list->setListTitle($this->txt("actions"));
        $current_selection_list->setId($id);
        $current_selection_list->setSelectionHeaderClass("small");
        $current_selection_list->setItemLinkClass("xsmall");
        $current_selection_list->setLinksMode("il_ContainerItemCommand2");
        $current_selection_list->setHeaderIcon(\ilAdvancedSelectionListGUI::DOWN_ARROW_DARK);
        $current_selection_list->setUseImages(false);
        $current_selection_list->setAdditionalToggleElement("id" . $id, "ilContainerListItemOuterHighlight");

        foreach ($this->getActionMenuItems($id, $crs_ref_id) as $value) {
            $current_selection_list->addItem($value["title"], "", $value["link"], $value["image"], "", $value["frame"]);
        }

        return $current_selection_list->getHTML();
    }

    abstract protected function prepareData() : array;


    protected function showConfirmForm(string $cmd, string $cancel_cmd, array $ids)
    {
        if ($cmd === self::CMD_REVOKE_CONFIRMED) {
            $msg = 'msg_confirm_revoke';
            $booking_request_ids = [];
            foreach ($ids as $id) {
                $booking_request_ids[$id] = $id;
            }
        } else {
            if ($cmd === self::CMD_DECLINE_CONFIRMED) {
                $msg = 'msg_confirm_decline';
            }
            if ($cmd === self::CMD_APPROVE_CONFIRMED) {
                $msg = 'msg_confirm_approve';
            }
            //$booking_request_ids are actually approval_ids!!
            foreach ($ids as $id) {
                $approval = array_shift($this->approval_actions->getApprovals([$id]));
                $booking_request_ids[$id] = $approval->getBookingRequestId();
            }
        }

        $confirmation = new \ilConfirmationGUI();
        $confirmation->setFormAction($this->g_ctrl->getFormAction($this));
        $confirmation->setHeaderText($this->txt($msg));
        $confirmation->setConfirm($this->txt("confirm"), $cmd);
        $confirmation->setCancel($this->txt("confirm_cancel"), $cancel_cmd);

        foreach ($ids as $id) {
            $booking_request = array_shift($this->approval_actions->getBookingRequests([$booking_request_ids[$id]]));
            $course_info_obj = new CourseInformation($booking_request->getCourseRefId());

            $facade = new ApprovalsFacade(
                $course_info_obj,
                [], // no approvals needed
                $booking_request,
                \ilObjectFactory::getInstanceByObjId($booking_request->getUserId()),
                false,
                null
            );
            $item_txt = implode(
                ', ',
                [
                $facade->getLastname(),
                $facade->getFirstname(),
                $facade->getEmail(),
                $facade->getCourseTitle(),
                $facade->getCourseDate()
                ]
            );
            $confirmation->addItem("booking_request_ids[]", $id, $item_txt);
        }

        $this->g_tpl->setContent($confirmation->getHtml());
    }
}
