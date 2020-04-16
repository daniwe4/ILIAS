<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\BookingApprovals\Events;

use CaT\Plugins\BookingApprovals\Approvals\BookingRequest;
use CaT\Plugins\BookingApprovals\Approvals\Approval;
use CaT\Plugins\BookingApprovals\Utils\IliasWrapper;
use ILIAS\TMS\Booking;

/**
 * fire events.
 */
class Events
{
    const EVENT_COMPONENT = 'Plugin/BookingApprovals';
    const COURSE_COMPONENT = 'Modules/Course';

    const USER_REQUESTED_COURSEBOOKING = 'request_created';
    const APPROVAL_APPROVED = 'approval_approved';
    const APPROVAL_DECLINED = 'approval_declined';
    const REQUEST_APPROVED = 'request_approved';
    const REQUEST_DECLINED = 'request_declined';
    const REQUEST_OUTDATED = 'request_outdated';
    const REQUEST_REVOKED = "request_revoked";
    const REQUEST_PROCESSING_FAILED = 'request_processing_failed';

    /**
     * @var \ilAppEventHandler
     */
    protected $g_event_handler;

    /**
     * @var	IliasWrapper
     */
    protected $ilias_wrapper;

    public function __construct(\ilAppEventHandler $g_event_handler, IliasWrapper $ilias_wrapper)
    {
        $this->g_event_handler = $g_event_handler;
        $this->ilias_wrapper = $ilias_wrapper;
    }

    public function fireEventBookingRequestCreated(BookingRequest $booking_request)
    {
        $event = self::USER_REQUESTED_COURSEBOOKING;
        $payload = array(
             'booking_request_id' => $booking_request->getId(),
             'obj_id' => $this->ilias_wrapper->lookupObjId($booking_request->getCourseRefId()),
             'usr_id' => $booking_request->getUserId()
        );
        $this->fireEvent($event, $payload);
    }

    public function fireEventBookingRequestStateApproved(BookingRequest $booking_request)
    {
        $event = self::REQUEST_APPROVED;
        $payload = array(
             'booking_request_id' => $booking_request->getId(),
             'obj_id' => $this->ilias_wrapper->lookupObjId($booking_request->getCourseRefId()),
             'usr_id' => $booking_request->getUserId()
        );
        $this->fireEvent($event, $payload);
    }

    public function fireEventBookingRequestStateDeclined(BookingRequest $booking_request)
    {
        $event = self::REQUEST_DECLINED;
        $payload = array(
             'booking_request_id' => $booking_request->getId(),
             'obj_id' => $this->ilias_wrapper->lookupObjId($booking_request->getCourseRefId()),
             'usr_id' => $booking_request->getUserId()
        );
        $this->fireEvent($event, $payload);
    }

    public function fireEventBookingRequestRevoked(BookingRequest $booking_request)
    {
        $event = self::REQUEST_REVOKED;
        $payload = array(
            'booking_request_id' => $booking_request->getId(),
            'obj_id' => $this->ilias_wrapper->lookupObjId($booking_request->getCourseRefId()),
            'usr_id' => $booking_request->getUserId()
        );
        $this->fireEvent($event, $payload);
    }

    public function fireEventApprovalApproved(Approval $approval)
    {
        $event = self::APPROVAL_APPROVED;
        $payload = array(
             'approval_id' => $approval->getId(),
        );
        $this->fireEvent($event, $payload);
    }

    public function fireEventApprovalDeclined(Approval $approval)
    {
        $event = self::APPROVAL_DECLINED;
        $payload = array(
             'approval_id' => $approval->getId(),
        );
        $this->fireEvent($event, $payload);
    }

    protected function fireEvent($event, $payload)
    {
        $this->g_event_handler->raise(
            self::EVENT_COMPONENT,
            $event,
            $payload
         );
    }

    /**
     * after successfull booking, relay to the system.
     */
    public function fireEventBookingSuccess(BookingRequest $booking_request)
    {
        $crs_ref_id = $booking_request->getCourseRefId();
        $obj_id = $this->ilias_wrapper->lookupObjId($booking_request->getCourseRefId());
        $usr_id = $booking_request->getUserId();

        if (\ilCourseWaitingList::_isOnList($usr_id, $obj_id)) {
            $event = Booking\Actions::EVENT_USER_BOOKED_WAITING;
            if ($booking_request->getUserId() !== $booking_request->getActingUserId()) {
                $event = Booking\Actions::EVENT_SUPERIOR_BOOKED_WAITING;
            }
        } else {
            $event = Booking\Actions::EVENT_USER_BOOKED_COURSE;
            if ($booking_request->getUserId() !== $booking_request->getActingUserId()) {
                $event = Booking\Actions::EVENT_SUPERIOR_BOOKED_COURSE;
            }
        }

        $payload = array(
             'booking_request_id' => $booking_request->getId(),
             'crs_ref_id' => $crs_ref_id,
             'obj_id' => $obj_id,
             'usr_id' => $usr_id
        );

        $this->g_event_handler->raise(self::COURSE_COMPONENT, $event, $payload);
    }
}
