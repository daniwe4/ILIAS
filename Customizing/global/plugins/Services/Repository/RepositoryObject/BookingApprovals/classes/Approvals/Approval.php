<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\BookingApprovals\Approvals;

/**
 * Approvals are assigned to BookingRequests; all Approvals must be
 * positively met ("approved") for a BookingRequest to be transferred
 * to an actual booking.
 */
class Approval
{
    const OPEN = 1;
    const APPROVED = 2;
    const DECLINED = 3;
    const OUTDATED = 4;
    const CANCELED_BY_USER = 5;
    const NO_NEXT_APPROVER = 6;

    protected $id;
    protected $booking_request_id;
    protected $order_number;
    protected $approval_position;
    protected $approving_state;
    protected $approving_usr_id;
    protected $approving_date;

    public function __construct(
        int $id,
        int $booking_request_id,
        int $order_number,
        int $approval_position,
        int $approving_state,
        int $approving_usr_id = null,
        \DateTime $approving_date = null
    ) {
        $this->id = $id;
        $this->booking_request_id = $booking_request_id;
        $this->order_number = $order_number;
        $this->approval_position = $approval_position;
        $this->checkValidState($approving_state);
        $this->approving_state = $approving_state;
        $this->approving_usr_id = $approving_usr_id;
        $this->approving_date = $approving_date;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getBookingRequestId() : int
    {
        return $this->booking_request_id;
    }

    public function getOrderNumber() : int
    {
        return $this->order_number;
    }

    public function getApprovalPosition() : int
    {
        return $this->approval_position;
    }

    public function isApproved() : bool
    {
        return $this->approving_state === static::APPROVED
            && !is_null($this->getApprovingUserId());
    }

    public function isOpen() : bool
    {
        return $this->approving_state === static::OPEN;
    }

    protected function checkValidState($state)
    {
        if (!in_array($state, [
            static::OPEN,
            static::APPROVED,
            static::DECLINED,
            //static::OUTDATED,
            static::CANCELED_BY_USER,
            static::NO_NEXT_APPROVER
        ])) {
            throw new \InvalidArgumentException("Invalid State: $state", 1);
        }
    }

    public function withState(int $state) : Approval
    {
        $this->checkValidState($state);
        $clone = clone $this;
        $clone->approving_state = $state;
        return $clone;
    }

    public function getState() : int
    {
        return $this->approving_state;
    }

    public function withApprovingUserId(int $approving_usr_id) : Approval
    {
        $clone = clone $this;
        $clone->approving_usr_id = $approving_usr_id;
        return $clone;
    }

    /**
     * @return int|null
     */
    public function getApprovingUserId()
    {
        return $this->approving_usr_id;
    }

    public function withApprovalDate(\DateTime $date) : Approval
    {
        $clone = clone $this;
        $clone->approving_date = $date;
        return $clone;
    }

    /**
     * @return \DateTime|null
     */
    public function getApprovalDate()
    {
        return $this->approving_date;
    }
}
