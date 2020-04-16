<?php

declare(strict_types=1);

use CaT\Plugins\BookingApprovals\Approvals\ApprovalGUI;
use CaT\Plugins\BookingApprovals\Approvals\Actions;
use CaT\Plugins\BookingApprovals\Approvals\ApprovalsFacade;
use CaT\Plugins\BookingApprovals\Approvals\Approval;
use CaT\Plugins\BookingApprovals\Approvals\BookingRequest;
use CaT\Plugins\BookingApprovals\Utils\CourseInformation;

/**
 * GUI for an overview of approving requests.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilApprovalsOverviewGUI extends ApprovalGUI
{
    const TEMPLATE_NAME = "tpl.approvals_overview.html";
    const TABLE_TITLE = "approvals_list";
    const APPROVER_ACTIONS = true;
    const REQUESTER_ACTIONS = false;
    const TABLE_ID = "approvals_list";

    /**
     * @throws Exception on unknown command.
     */
    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd(self::CMD_SHOW_OVERVIEW);
        $this->command = $cmd;
        switch ($cmd) {
            case self::CMD_SHOW_OVERVIEW:
                $this->$cmd();
                break;
            case self::CMD_APPROVE:
                $this->confirm(self::CMD_APPROVE_CONFIRMED);
                break;
            case self::CMD_DECLINE:
                $this->confirm(self::CMD_DECLINE_CONFIRMED);
                break;
            case self::CMD_APPROVE_CONFIRMED:
                $this->approve();
                // no break
            case self::CMD_DECLINE_CONFIRMED:
                $this->decline();
                break;
            default:
                throw new Exception(__METHOD__ . " unkown command " . $cmd);
        }
    }

    public function showContent()
    {
        $table = $this->getTable();
        $table = $this->addTableStandardRows($table);
        $table->addColumn($this->txt(self::S_ACTIONS));

        /* 2do: unused?
        $limit = (int)$table->getLimit();
        $offset = (int)$table->getOffset();
        */

        $order_field = $table->getOrderField();
        $order_direction = $table->getOrderDirection();

        $approvals = $this->prepareData();
        $approvals = $this->sortData($approvals, $order_field, $order_direction);
        $table->setData($approvals);
        //$table->setMaxCount($this->actions->getMaxApprovals());

        $this->g_tpl->setContent($table->getHtml());
    }

    protected function confirm(string $cmd)
    {
        $error = array();
        $approval_ids = $this->getBookingRequestIds(); //actually: approval_ids

        if (count($approval_ids) == 0) {
            \ilUtil::sendInfo($this->txt("no_selection"), true);
            $this->g_ctrl->redirect($this, self::CMD_SHOW_OVERVIEW);
        }

        $this->showConfirmForm($cmd, self::CMD_SHOW_OVERVIEW, $approval_ids);
    }

    protected function approve()
    {
        $error = array();
        $info = array();
        $success = array();
        $approvals = $this->getApprovals();

        foreach ($approvals as $approval) {
            if (!$this->approval_actions->mayUserApproveFor((int) $this->g_usr->getId(), $approval)) {
                $error[] = $approval->getBookingRequestId();
                continue;
            }

            $this->approval_actions->approve($approval, (int) $this->g_usr->getId());
            $success[] = $approval->getBookingRequestId();

            if (!$this->nextApproverExists($approval->getBookingRequestId())) {
                $this->approval_actions->setBookingRequestState(
                    $approval->getBookingRequestId(),
                    BookingRequest::NO_NEXT_APPROVER
                );
                $info[] = $approval->getBookingRequestId();
            }
        }

        $this->redirectWithMessage(self::CMD_APPROVE_CONFIRMED, $success, $info, $error);
    }

    protected function nextApproverExists(int $booking_request_id) : bool
    {
        $approvals = $this->approval_actions->getApprovalsForBookingRequestId($booking_request_id);
        $booking_requests = $this->approval_actions->getBookingRequests([$booking_request_id]);
        $booking_request = array_shift($booking_requests);

        //only open ones are interesting
        $open_approvals = array_filter(
            $approvals,
            function ($approval) {
                return $approval->isOpen();
            }
        );

        //nothing to do, if they are all closed:
        if (count($open_approvals) === 0) {
            return true;
        }

        //otherwise, get next open one:
        $approval = array_shift($open_approvals);
        $position = $approval->getApprovalPosition();
        $users = array_unique(
            $this->orgu_utils->getNextHigherUsersWithPositionForUser(
                $position,
                $booking_request->getUserId()
            )
        );

        if (count($users) == 0) {
            $approval = $approval->withState(Approval::NO_NEXT_APPROVER);
            $this->approval_actions->updateApproval($approval);
            return false;
        }

        return true;
    }

    protected function decline()
    {
        $success = array();
        $info = array();
        $error = array();
        $approvals = $this->getApprovals();

        foreach ($approvals as $approval) {
            if (!$this->approval_actions->mayUserApproveFor((int) $this->g_usr->getId(), $approval)) {
                $error[] = $approval->getBookingRequestId();
                continue;
            }
            $this->approval_actions->decline($approval, (int) $this->g_usr->getId());
        }
        $this->redirectWithMessage(self::CMD_DECLINE_CONFIRMED, $success, $info, $error);
    }

    protected function getApprovals()
    {
        $approval_ids = $this->getBookingRequestIds(); //actually: approval_ids
        return $this->approval_actions->getApprovals($approval_ids);
    }

    /**
     * @return int[]
     */
    protected function getBookingRequestIds() : array
    {
        $booking_request_id = $_GET["booking_request_id"];
        $booking_request_ids = $_POST["booking_request_ids"];
        $ret = array();

        if (isset($booking_request_id) && $booking_request_id !== 0) {
            $ret[] = $booking_request_id;
        }

        if (is_array($booking_request_ids)) {
            $ret = $booking_request_ids;
        }

        return array_map('intval', $ret);
    }

    protected function redirectWithMessage(
        string $cmd,
        array $success = [],
        array $no_next_approver = [],
        array $error = []
    ) {
        if (count($error) != 0) {
            $booking_requests = $this->approval_actions->getBookingRequests($error);

            \ilUtil::sendFailure(
                $this->getMessage(
                    $this->txt("no_approval_permission"),
                    $booking_requests
                ),
                true
            );
        }

        if (count($no_next_approver) > 0) {
            $booking_requests = $this->approval_actions->getBookingRequests($no_next_approver);

            \ilUtil::sendInfo(
                $this->getMessage(
                    $this->txt("no_next_approver"),
                    $booking_requests
                ),
                true
            );
        }

        if (count($success) > 0) {
            $booking_requests = $this->approval_actions->getBookingRequests($success);

            \ilUtil::sendSuccess(
                $this->getMessage(
                    $this->txt("successfuly_approved"),
                    $booking_requests
                ),
                true
            );
        }

        $this->g_ctrl->redirect($this, self::CMD_SHOW_OVERVIEW);
    }

    protected function getMessage(string $title, array $booking_requests) : string
    {
        $plugin_dir = $this->actions->getObject()->getPluginObject()->getDirectory();
        $tpl = new ilTemplate("tpl.message.html", true, true, $plugin_dir);
        $tpl->setVariable("TITLE", $title);
        $tpl->setCurrentBlock("messages");
        foreach ($booking_requests as $booking_request) {
            $tpl->setVariable("MESSAGE", ilObjUser::_lookupFullname($booking_request->getUserId()));
        }
        $tpl->parseCurrentBlock();

        return $tpl->get();
    }

    protected function prepareData() : array
    {
        $approval_facades = array();
        $booking_requests = $this->approval_actions->selectBookingRequests(array(), true);

        foreach ($booking_requests as $booking_request) {
            $positions = array();
            $matching = false;
            $may_act = false;
            $next_id = null;

            $approvals = $this->approval_actions->getApprovalsForBookingRequestId($booking_request->getId());
            $user_id = (int) $booking_request->getUserId();
            $next = $this->getNextOpenApproval($approvals);

            foreach ($approvals as $approval) {
                $pos = $approval->getApprovalPosition();
                $ids = $this->orgu_utils->getNextHigherUsersWithPositionForUser($pos, $user_id);
                if (in_array((int) $this->g_usr->getId(), $ids)) {
                    $matching = true;
                    if ($next == $approval) {
                        $may_act = true;
                        $next_id = $approval->getId();
                    }
                }
            }

            if (!$matching) {
                continue;
            }

            $approval_facades[] = new ApprovalsFacade(
                new CourseInformation($booking_request->getCourseRefId()),
                $approvals,
                $booking_request,
                ilObjectFactory::getInstanceByObjId($user_id),
                $may_act,
                $next_id
            );
        }

        return $approval_facades;
    }

    /**
     * @inheritdoc
     */
    protected function tableId() : string
    {
        return self::TABLE_ID;
    }

    /**
     * @inheritdoc
     */
    protected function fillRow()
    {
        return function ($table, $set) {
            $disabled = "";
            if (!$set->mayAct()) {
                $disabled = "disabled";
            }

            $tpl = $table->getTemplate();
            $tpl->setVariable("DISABLED", $disabled);
            $tpl->setVariable("ROW_SELECTION_POSTVAR", "booking_request_ids");
            $tpl->setVariable("BOOKING_REQUEST_ID", $set->getApprovalIdForAction());
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
                        sprintf("(%s %s)", ilObjUser::_lookupFullname($user_id), $dat)
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

            if ($set->mayAct()) {
                $tpl->setVariable("ACTIONS", $this->getActionMenu($set->getApprovalIdForAction(), $set->getCourseRefId()));
            }
        };
    }



    protected function getActionMenuItems(int $id, int $crs_ref_id) : array
    {
        $items = array();

        $this->g_ctrl->setParameter($this->parent, "booking_request_id", $id);
        $link_approve = $this->g_ctrl->getLinkTargetByClass(array("ilApprovalsOverviewGUI"), self::CMD_APPROVE);
        $link_decline = $this->g_ctrl->getLinkTargetByClass(array("ilApprovalsOverviewGUI"), self::CMD_DECLINE);
        $this->g_ctrl->clearParameters($this->parent);

        $items[] = array("title" => $this->txt("approve"), "link" => $link_approve, "image" => "", "frame" => "");
        $items[] = array("title" => $this->txt("decline"), "link" => $link_decline, "image" => "", "frame" => "");

        if (
            $this->g_access->checkAccess("read", "", $crs_ref_id)
        ) {
            require_once "Services/Link/classes/class.ilLink.php";
            $link_course = ilLink::_getStaticLink($crs_ref_id, "crs");
            $items[] = array("title" => $this->txt("to_course"), "link" => $link_course, "image" => "", "frame" => "_blank");
        }

        return $items;
    }
}
