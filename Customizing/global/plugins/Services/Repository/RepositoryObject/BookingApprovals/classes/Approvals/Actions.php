<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\BookingApprovals\Approvals;

use CaT\Plugins\BookingApprovals\Utils\OrguUtils;
use CaT\Plugins\BookingApprovals\Events\Events as ApprovalEvents;

/**
 * Actions to read and update BookingRequests and Approvals.
 */
class Actions
{
    public function __construct(
        ApprovalDB $db,
        ApprovalEvents $events,
        OrguUtils $orgu_utils
    ) {
        $this->db = $db;
        $this->events = $events;
        $this->orgu_utils = $orgu_utils;
    }

    /**
     * Create a BookingRequest; also, create Approvals to be filled.
     *
     * @param int $usr_id
     * @param int $crs_ref_id
     * @param array[<int>,<int>] $approvals  //$order=>$position_id
     * @param string $booking_payload
     *
     * @return bool //?BookingRequest ?Approvals
     */
    public function requestBooking(
        int $acting_usr_id,
        int $usr_id,
        int $crs_ref_id,
        int $crs_id,
        array $approvals,
        string $booking_payload
    ) {
        if (count($approvals) === 0) {
            throw new \InvalidArgumentException("Cannot create BookingRequests without assigned approvements.", 1);
        }
        $booking_request = $this->db->createBookingRequest(
            $acting_usr_id,
            $usr_id,
            $crs_ref_id,
            $crs_id,
            $booking_payload
        );
        $br_id = $booking_request->getId();

        $needed_approvals = [];
        foreach ($approvals as $order => $position_id) {
            $needed_approvals[] = $this->db->createApproval($br_id, $order, $position_id);
        }

        $this->events->fireEventBookingRequestCreated($booking_request);
        return true;
    }

    public function approve(Approval $approval, int $acting_usr_id)
    {
        $now = new \DateTime('now');

        $approval = $approval
            ->withState(Approval::APPROVED)
            ->withApprovingUserId($acting_usr_id)
            ->withApprovalDate($now)
        ;

        $this->db->updateApproval($approval);

        $this->events->fireEventApprovalApproved($approval);

        $this->setBookingRequestStateByApprovals($approval->getBookingRequestId());
    }

    public function setBookingRequestState(int $booking_request_id, int $status)
    {
        $booking_requests = $this->getBookingRequests([$booking_request_id]);
        $booking_request = array_shift($booking_requests);
        $booking_request = $booking_request->withState($status);
        $this->db->updateBookingRequest($booking_request);
    }

    public function updateApproval(Approval $approval)
    {
        $now = new \DateTime('now');
        $approval = $approval->withApprovalDate($now);
        $this->db->updateApproval($approval);
    }

    public function decline(Approval $approval, int $acting_usr_id)
    {
        $now = new \DateTime('now');

        $approval = $approval
            ->withState(Approval::DECLINED)
            ->withApprovingUserId($acting_usr_id)
            ->withApprovalDate($now)
        ;

        $this->db->updateApproval($approval);

        $this->events->fireEventApprovalDeclined($approval);

        $this->setBookingRequestStateByApprovals($approval->getBookingRequestId());
    }

    public function revoke(BookingRequest $booking_request, bool $by_user = true)
    {
        $booking_request = $booking_request->withState(BookingRequest::CANCELED_BY_USER);
        $this->db->updateBookingRequest($booking_request);

        $connected_approvals = $this->getApprovalsForBookingRequestId($booking_request->getId());
        $now = new \DateTime('now');
        foreach ($connected_approvals as $approval) {
            $approval = $approval
                ->withState(Approval::CANCELED_BY_USER)
                ->withApprovalDate($now);
            $this->db->updateApproval($approval);
        }
        $this->events->fireEventBookingRequestRevoked($booking_request);
    }

    public function setBookingRequestStateByApprovals(int $booking_request_id)
    {
        $approvals = $this->db->getApprovalsForRequest($booking_request_id);
        $state = BookingRequest::APPROVED;
        foreach ($approvals as $approval) {
            if ($approval->getState() === Approval::OPEN) {
                $state = BookingRequest::OPEN;
                break;
            }
            if ($approval->getState() === Approval::DECLINED) {
                $state = BookingRequest::DECLINED;
                break;
            }
        }

        //do not update if storno/outdated/etc?
        $booking_requests = $this->db->getBookingRequests(array($booking_request_id));
        $booking_request = array_pop($booking_requests);
        if ($booking_request->getState() !== $state) {
            $booking_request = $booking_request->withState($state);
            $this->db->updateBookingRequest($booking_request);

            switch ($state) {
                case BookingRequest::APPROVED:
                    $this->events->fireEventBookingRequestStateApproved($booking_request);
                    break;
                case BookingRequest::DECLINED:
                    $this->events->fireEventBookingRequestStateDeclined($booking_request);
                    break;
            }
        }
    }

    public function mayUserApproveFor(int $approver_id, Approval $approval) : bool
    {
        $request = $this->db->getBookingRequests(array($approval->getBookingRequestId()));
        $possible_approver_ids = $this->orgu_utils->getNextHigherUsersWithPositionForUser(
            $approval->getApprovalPosition(),
            $request[0]->getUserId()
        );

        return in_array($approver_id, $possible_approver_ids);
    }

    /**
     * @return Approval[]
     */
    public function getApprovals(array $approval_ids) : array
    {
        return $this->db->getApprovals($approval_ids);
    }

    /**
     * @return BookinRequest[]
     */
    public function getAllBookingRequets() : array
    {
        return $this->db->getAllBookingRequets();
    }

    /**
     * @return BookingRequest[]
     */
    public function selectBookingRequests(array $user_ids, bool $open = true) : array
    {
        return $this->db->selectBookingRequests($user_ids, $open);
    }

    /**
     * @return BookingRequest[]
     */
    public function getBookingRequests(array $booking_request_ids) : array
    {
        return $this->db->getBookingRequests($booking_request_ids);
    }

    public function getApprovalsForBookingRequestId(int $booking_request_id) : array
    {
        return $this->db->getApprovalsForRequest($booking_request_id);
    }
}
