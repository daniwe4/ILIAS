<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\BookingApprovals\Approvals;

/**
 * Storage for BookingRequests and Approvals
 */
interface ApprovalDB
{
    public function createBookingRequest(
        int $acting_usr_id,
        int $usr_id,
        int $crs_ref_id,
        int $crs_id,
        string $booking_data
    ) : BookingRequest;

    public function updateBookingRequest(BookingRequest $booking_request);

    /**
     * @return BookingRequest[]
     */
    public function getBookingRequests(array $ids) : array;

    public function createApproval(
        int $booking_request_id,
        int $order_number,
        int $approval_position
    ) : Approval;

    public function updateApproval(Approval $approval);

    /**
     * @return Approval[]
     */
    public function getApprovalsForRequest(int $request_id) : array;

    public function hasUserOpenRequestOnCourse(int $usr_id, int $crs_id) : bool;
}
