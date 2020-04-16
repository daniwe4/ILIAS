<?php

declare(strict_types=1);

use CaT\Plugins\BookingApprovals\Approvals\ApprovalGUI;
use CaT\Plugins\BookingApprovals\Approvals\Actions;
use CaT\Plugins\BookingApprovals\Approvals\ApprovalsFacade;
use CaT\Plugins\BookingApprovals\Approvals\Approval;
use CaT\Plugins\BookingApprovals\Approvals\BookingRequest;
use CaT\Plugins\BookingApprovals\Utils\CourseInformation;

/**
 * GUI class for showing finished approvals.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilMyApprovalsOverviewGUI extends ApprovalGUI
{
    const TEMPLATE_NAME = "tpl.approvals_overview.html";
    const TABLE_TITLE = "my_approvals_list";
    const APPROVER_ACTIONS = false;
    const REQUESTER_ACTIONS = true;
    const TABLE_ID = "my_approvals_overview";

    /**
     * @throws Exception Command is unknwon.
     */
    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd(self::CMD_SHOW_MY_APPROVALS);
        $this->command = $cmd;
        switch ($cmd) {
            case self::CMD_SHOW_MY_APPROVALS:
                $this->$cmd();
                break;
            case self::CMD_REVOKE:
                $this->confirm();
                break;
            case self::CMD_REVOKE_CONFIRMED:
                $this->revoke();
                break;

            default:
                throw new Exception(__METHOD__ . " unkown command " . $cmd);
        }
    }

    public function showMyApprovals()
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

    protected function prepareData() : array
    {
        $approval_facades = array();
        $booking_requests = array();
        $blocked_booking_requests = array();

        $booking_requests = $this->approval_actions->selectBookingRequests(
            array((int) $this->g_usr->getId()),
            true
        );
        $blocked_booking_requests = $this->approval_actions->selectBookingRequests(
            array((int) $this->g_usr->getId()),
            false
        );
        $blocked_booking_requests = array_filter($blocked_booking_requests, function ($request) {
            return $request->getState() === BookingRequest::NO_NEXT_APPROVER;
        });

        $booking_requests = array_merge($booking_requests, $blocked_booking_requests);

        foreach ($booking_requests as $booking_request) {
            $user_id = (int) $booking_request->getUserId();
            $approvals = $this->approval_actions->getApprovalsForBookingRequestId($booking_request->getId());
            $positions = array();

            $approval_facades[] = new ApprovalsFacade(
                new CourseInformation($booking_request->getCourseRefId()),
                $approvals,
                $booking_request,
                ilObjectFactory::getInstanceByObjId($user_id),
                false,
                null
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


    protected function getActionMenuItems(int $id, int $crs_ref_id) : array
    {
        $items = array();

        $this->g_ctrl->setParameter($this->parent, "booking_request_id", $id);
        $link_revoke = $this->g_ctrl->getLinkTargetByClass(array("ilMyApprovalsOverviewGUI"), self::CMD_REVOKE);
        $this->g_ctrl->clearParameters($this->parent);

        $items[] = array("title" => $this->txt("revoke"), "link" => $link_revoke, "image" => "", "frame" => "");
        return $items;
    }

    protected function confirm()
    {
        $error = array();
        $booking_request_ids = $this->getBookingRequestIdsFromRequest();

        if (count($booking_request_ids) == 0) {
            \ilUtil::sendInfo($this->txt("revoke_no_selection"), true);
            $this->g_ctrl->redirect($this, self::CMD_SHOW_MY_APPROVALS);
        }

        $this->showConfirmForm(
            self::CMD_REVOKE_CONFIRMED,
            self::CMD_SHOW_MY_APPROVALS,
            $booking_request_ids
        );
    }

    protected function revoke()
    {
        $booking_request_ids = $this->getBookingRequestIdsFromRequest();
        $booking_requests = $this->approval_actions->getBookingRequests($booking_request_ids);
        foreach ($booking_requests as $booking_request) {
            $this->approval_actions->revoke($booking_request);
        }
        \ilUtil::sendSuccess($this->txt("revoke_success"), true);
        $this->g_ctrl->redirect($this, self::CMD_SHOW_MY_APPROVALS);
    }

    /**
     * @return int[]
     */
    protected function getBookingRequestIdsFromRequest() : array
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

        $ret = array_map('intval', $ret);
        return $ret;
    }
}
