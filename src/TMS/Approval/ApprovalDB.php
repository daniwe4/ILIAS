<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

namespace ILIAS\TMS\Approval;

/**
 * Storage for BookingRequests and Approvals
 */
interface ApprovalDB
{

	public function createBookingRequest(
		int $usr_id,
		int $crs_ref_id,
		string $booking_data
	) : BookingRequest;

	public function updateBookingRequest(BookingRequest $booking_request);

	/**
	 * @return BookingRequest[]
	 */
	public function selectBookingRequests(
		array $usr_id = [],
		array $crs_ref_id = [],
		array $state = []
	) : array;

	/**
	 * @return BookingRequest[]
	 */
	public function getBookingRequests(array $id) : array;

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

}
