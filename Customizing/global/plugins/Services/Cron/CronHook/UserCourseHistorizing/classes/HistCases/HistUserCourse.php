<?php

namespace CaT\Plugins\UserCourseHistorizing\HistCases;

use CaT\Historization\HistCase\HistCase as HistCase;
use CaT\Historization\Event\Event as Event;
use CaT\Plugins\UserCourseHistorizing\Digesters as Digesters;

class HistUserCourse implements HistCase
{
    const BOOKING_STATUS_PARTICIPANT = 'participant';
    const BOOKING_STATUS_CANCELLED = 'cancelled';
    const BOOKING_STATUS_WAITING_CANCELLED = 'waiting_cancelled';
    const BOOKING_STATUS_WAITING_SELF_CANCELLED = 'waiting_self_cancelled';
    const BOOKING_STATUS_CANCELLED_AFTER_DEADLINE = 'cancelled_after_deadline';
    const BOOKING_STATUS_WAITING = 'waiting';

    const PARTICIPATION_STATUS_NONE = 'none';
    const PARTICIPATION_STATUS_IN_PROGRESS = 'in_progress';
    const PARTICIPATION_STATUS_SUCCESSFUL = 'successful';
    const PARTICIPATION_STATUS_ABSENT = 'absent';

    const APPROVAL_REQUEST_CREATED = 'approval_pending';
    const APPROVAL_REQUEST_DECLINED = 'approval_declined';
    const APPROVAL_REQUEST_APPROVED = 'approval_approved';
    const APPROVAL_REQUEST_REVOKED = 'approval_revoked';

    protected static $skip_courses = [];


    /**
     * The title of the case, should be unique among all the cases.
     *
     * @return	string
     */
    public function title()
    {
        return 'usrcrs';
    }

    /**
     * Get the field names describing the id of the case. The actual id
     * values are a part of the payload.
     *
     * @return	string[]
     */
    public function id()
    {
        return ['crs_id','usr_id'];
    }

    /**
     * Get all the fields stored corresponding to this service.
     *
     * @return	string[]
     */
    public function fields()
    {
        return [
            'crs_id','usr_id',
            'booking_status',
            'participation_status',
            'custom_p_status',
            'created_ts',
            'creator',
            'nights',
            'booking_date',
            'ps_acquired_date',
            'idd_learning_time',
            'prior_night',
            'following_night',
            'cancel_booking_date',
            'waiting_date',
            'cancel_waiting_date',
            'wbd_booking_id',
            'cancellation_fee',
            'roles'
        ];
    }

    /**
     * Get payload fields only. Skip timestamp, created user, id.
     *
     * @return	string[]
     */
    public function payloadFields()
    {
        return [
            'booking_status',
            'participation_status',
            'custom_p_status',
            'nights',
            'booking_date',
            'ps_acquired_date',
            'idd_learning_time',
            'prior_night',
            'following_night',
            'cancel_booking_date',
            'waiting_date',
            'cancel_waiting_date',
            'wbd_booking_id',
            'cancellation_fee',
            'roles'
        ];
    }

    /**
     * Get the format of any field.
     *
     * @return	string
     */
    public function typeOfField($field)
    {
        switch ($field) {
            case 'crs_id':
            case 'usr_id':
            case 'creator':
            case 'idd_learning_time':
            case 'prior_night':
            case 'following_night':
                return self::HIST_TYPE_INT;
            case 'created_ts':
                return self::HIST_TYPE_TIMESTAMP;
            case 'booking_status':
            case 'participation_status':
            case 'custom_p_status':
            case 'wbd_booking_id':
                return self::HIST_TYPE_STRING;
            case 'nights':
            case 'roles':
                return self::HIST_TYPE_LIST_STRING;
            case 'booking_date':
            case 'ps_acquired_date':
            case 'cancel_booking_date':
            case 'waiting_date':
            case 'cancel_waiting_date':
                return self::HIST_TYPE_DATE;
            case 'cancellation_fee':
                return self::HIST_TYPE_FLOAT;
        }
    }

    /**
     * Check wether an event is relevant for this case.
     *
     * @return	bool
     */
    public function isEventRelevant(Event $event)
    {
        $location = $event->location();
        $type = $event->type();
        $payload = $event->payload();
        return 	($location === 'Modules/Course'
                && (
                    $type === 'addToWaitingList'
                        || $type === 'removeFromWaitingList'
                        || $type === 'user_canceled_self_from_waiting'
                        || $type === 'superior_canceled_user_from_waiting'
                        || $type === 'historizeLocalRoles'
                )
                )
            || ($location === 'Services/AccessControl'
                && ($type === 'assignUser' || $type === 'deassignUser')
                && $payload['type'] === 'crs')
            || (
                $location === "Services/Tracking" &&
                    $type === "updateStatus" &&
                    $this->isCourse($payload['obj_id']) &&
                    !$this->shouldBeSkipped($payload['obj_id'])
            )
            || ($location === "Plugin/Accomodation" && $type === 'updateReservations')
            || ($location === "Plugin/CourseMember" && $type === 'closeList')
            || ($location === "Plugin/BookingApprovals"
                && ($type === 'request_created' || $type === 'request_declined' || $type === 'request_approved' || $type === 'request_revoked'))
            || (
                $location === 'Plugin/WBDInterface'
                && (
                    $type === 'addWBDBookingId' ||
                    $type === 'importParticipationWBD' ||
                    $type === 'removeWBDBookingId'
                )
            )
            || ($location === "Plugin/Accounting" && $type === 'userCancellationFee')
            || (
                $location === "Services/Object" &&
                $type === 'beforeDeletion' &&
                array_key_exists('object', $payload) &&
                $payload["object"]->getType() === 'crs'
            )
        ;
    }

    protected function isCourse($obj_id)
    {
        if (is_numeric($obj_id)) {
            return \ilObject::_lookupType($obj_id, false) === 'crs';
        }
        return false;
    }

    /**
     * Add relevant digesters to a given event.
     *
     * @param	Event	$event
     * @return	Event
     */
    public function addDigesters(Event $event)
    {
        switch ($event->type()) {
            case 'assignUser':
            case 'deassignUser':
                $event = $event->withDigester(new Digesters\BookingStatusDigester($event->type()))
                    ->withDigester(new Digesters\LocalRoleDigester($event->type()))
                ;
                break;
            case 'addToWaitingList':
            case 'removeFromWaitingList':
            case 'user_canceled_self_from_waiting':
            case 'superior_canceled_user_from_waiting':
            case 'request_created': //BookingApprovals
            case 'request_approved': //BookingApprovals
            case 'request_declined': //BookingApprovals
            case 'request_revoked': //BookingApprovals
                $event = $event->withDigester(new Digesters\BookingStatusDigester($event->type()));
                break;
            case 'updateStatus':
                $event = $event->withDigester(new Digesters\ParticipationStatusDigester());
                break;
            case 'updateReservations':
                $event = $event->withDigester(new Digesters\OvernightsDigester());
                break;
            case 'closeList':
                $event = $event->withDigester(new Digesters\IDDDigester());
                break;
            case 'addWBDBookingId':
            case 'removeWBDBookingId':
            case 'importParticipationWBD':
                break;
            case 'userCancellationFee':
                $event = $event->withDigester(new Digesters\UserCancellationFeeDigester());
                break;
            case 'historizeLocalRoles':
                $event = $event->withDigester(new Digesters\LocalRoleDigester($event->type()));
                break;
        }
        return 	$event
                    ->withDigester(new Digesters\CourseIdDigester())
                    ->withDigester(new Digesters\UserIdDigester())
                    ->withDigester(new Digesters\CreatedTSDigester())
                    ->withDigester(new Digesters\CreatorDigester());
    }

    /**
     * Get to know, wether this case uses a buffer.
     *
     * @return	bool
     */
    public function isBuffered()
    {
        return true;
    }

    /**
     * Fields relevant for case tracking.
     *
     * @return	string
     */
    public function creatorField()
    {
        return 'creator';
    }

    public function timestampField()
    {
        return 'created_ts';
    }

    /**
     * Are all relevant case id fields defined in the argument?
     *
     * @param	string|int[string]	$case_id
     * @return	bool
     */
    public function caseIdComplete(array $case_id)
    {
        return isset($case_id['crs_id']);
    }

    /**
     * Sometimes a value may not be set at a case,
     * in opposite to not being provided, since not relevant
     * at current change. One needs a proper notation for the
     * former to distinguish from the latter, which will allways
     * be denoted as null.
     *
     * @param	string	$type
     * @return	mixed
     */
    public function noValueEntryForFieldType($type)
    {
        return null;
    }

    public function markCourseToSkip(int $ref_id, int $obj_id)
    {
        self::$skip_courses[$ref_id] = $obj_id;
    }

    public function removeMarkCourseToSkip(int $ref_id)
    {
        unset(self::$skip_courses[$ref_id]);
    }

    protected function shouldBeSkipped(int $obj_id) : bool
    {
        return in_array($obj_id, self::$skip_courses);
    }
}
