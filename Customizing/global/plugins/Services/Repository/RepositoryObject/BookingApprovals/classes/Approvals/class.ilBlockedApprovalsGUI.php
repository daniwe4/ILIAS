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
 */
class ilBlockedApprovalsGUI extends ApprovalGUI
{
    const TEMPLATE_NAME = "tpl.approvals_overview.html";
    const TABLE_TITLE = "blocked_approvals_list";
    const TABLE_TITLE_USER = "my_blocked_approvals_list";
    //const MULTI_ACTIONS = false;
    const APPROVER_ACTIONS = false;
    const REQUESTER_ACTIONS = false;
    const TABLE_ID = "blocked_approvals";
    const TABLE_ID_SUPERIOR = "blocked_approvals_superior";

    /**
     * @throws Exception Command is unknwon.
     */
    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd();
        $this->command = $cmd;
        switch ($cmd) {
            case self::CMD_SHOW_BLOCKED_APPROVALS:
                $this->$cmd();
                break;
            default:
                throw new Exception(__METHOD__ . " unkown command " . $cmd);
        }
    }

    protected function getTableTitle() : string
    {
        if ($this->actions->getSettings()->getSuperiorView()) {
            return $this->txt(self::TABLE_TITLE);
        } else {
            return $this->txt(self::TABLE_TITLE_USER);
        }
    }

    public function showBlockedApprovals()
    {
        $table = $this->getTable();
        $table = $this->addTableStandardRows($table);

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
        $settings = $this->actions->getSettings();

        if ($settings->getSuperiorView()) {
            $booking_requests = $this->approval_actions->selectBookingRequests(
                array(),
                false
            );
        } else {
            $booking_requests = $this->approval_actions->selectBookingRequests(
                array((int) $this->g_usr->getId()),
                false
            );
        }

        foreach ($booking_requests as $booking_request) {
            if ($booking_request->getState() == BookingRequest::NO_NEXT_APPROVER) {
                $may_act = false;
                $next_id = null;

                $approvals = $this->approval_actions->getApprovalsForBookingRequestId($booking_request->getId());
                $positions = array();
                $user_id = (int) $booking_request->getUserId();

                if ($settings->getSuperiorView()) {
                    $matching = false;
                    $next = $this->getNextOpenApproval($approvals);
                    foreach ($approvals as $approval) {
                        $pos = $approval->getApprovalPosition();
                        $ids = $this->orgu_utils->getNextHigherUsersWithPositionForUser($pos, $user_id);
                        if (in_array((int) $this->g_usr->getId(), $ids)) {
                            $matching = true;
                        }
                    }
                    if (!$matching) {
                        continue;
                    }
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
        }

        return $approval_facades;
    }

    /**
     * @inheritdoc
     */
    protected function tableId() : string
    {
        $settings = $this->actions->getSettings();

        if ($settings->getSuperiorView()) {
            return self::TABLE_ID_SUPERIOR;
        }
        return self::TABLE_ID;
    }
}
