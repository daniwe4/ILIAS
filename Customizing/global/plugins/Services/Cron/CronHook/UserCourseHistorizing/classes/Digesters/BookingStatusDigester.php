<?php

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use CaT\Plugins\UserCourseHistorizing\HistCases\HistUserCourse;
use CaT\Historization\Digester\Digester as Digester;

class BookingStatusDigester implements Digester
{
    private $event;

    public function __construct($event)
    {
        assert('is_string($event)');
        $this->event = $event;
    }

    public function digest(array $payload)
    {
        $usr_id = $payload['usr_id'];
        $crs_id = $payload['obj_id'];
        $return = [];
        switch ($this->event) {
            case 'assignUser':
                if ($this->memberRole($payload['role_id'])) {
                    $return['booking_status'] = HistUserCourse::BOOKING_STATUS_PARTICIPANT;
                    $return['booking_date'] = date('Y-m-d');
                }
                break;
            case 'deassignUser':
                if ($this->memberRole($payload['role_id'])) {
                    $return['cancel_booking_date'] = date('Y-m-d');
                    if ($this->cancellationAfterDeadline($crs_id)) {
                        $return['booking_status'] = HistUserCourse::BOOKING_STATUS_CANCELLED_AFTER_DEADLINE;
                    } else {
                        $return['booking_status'] = HistUserCourse::BOOKING_STATUS_CANCELLED;
                    }
                }
                break;
            case 'addToWaitingList':
                $return['booking_status'] = HistUserCourse::BOOKING_STATUS_WAITING;
                $return['waiting_date'] = date('Y-m-d');
                break;
            case 'removeFromWaitingList':
                $return['cancel_waiting_date'] = date('Y-m-d');
                if (\ilParticipant::lookupStatusByMembershipRoleType($crs_id, $usr_id, \ilParticipant::MEMBERSHIP_MEMBER)) {
                    $return['booking_status'] = HistUserCourse::BOOKING_STATUS_PARTICIPANT;
                    $return['booking_date'] = date('Y-m-d');
                } else {
                    $return['booking_status'] = HistUserCourse::BOOKING_STATUS_WAITING_CANCELLED;
                }
                break;
            case 'user_canceled_self_from_waiting':
            case 'superior_canceled_user_from_waiting':
                $return['booking_status'] = HistUserCourse::BOOKING_STATUS_WAITING_SELF_CANCELLED;
                $return['cancel_waiting_date'] = date('Y-m-d');
                break;
            case 'request_created':
                $return['booking_status'] = HistUserCourse::APPROVAL_REQUEST_CREATED;
                break;
            case 'request_declined':
                $return['booking_status'] = HistUserCourse::APPROVAL_REQUEST_DECLINED;
                break;
            case 'request_approved':
                $return['booking_status'] = HistUserCourse::BOOKING_STATUS_PARTICIPANT;
                break;
            case 'request_revoked':
                $return['booking_status'] = HistUserCourse::APPROVAL_REQUEST_REVOKED;
                break;
        }
        return $return;
    }

    protected function cancellationAfterDeadline($crs_id)
    {
        assert('is_int($crs_id)');
        $crs_start = \ilObjectFactory::getInstanceByObjId($crs_id)->getCourseStart();
        if (!$crs_start) {
            return false;
        }
        $crs_start_date = new \DateTime($crs_start->get(IL_CAL_DATE));
        $ref_ids = \ilObject::_getAllReferences($crs_id);
        $ref_id = array_shift($ref_ids);
        if (!$ref_id) {
            throw new \Exception('undefined ref_id for crs ' . $crs_id);
        }
        global $DIC;
        $tree = $DIC['tree'];
        $bm_ref_id = array_shift($tree->getSubTree($tree->getNodeData($ref_id), false, 'xbkm'));
        if (!$bm_ref_id) {
            return false;
        }
        $storno_dl = \ilObjectFactory::getInstanceByRefId($bm_ref_id)->getStorno()->getDeadline();
        return $crs_start_date->sub(new \DateInterval('P' . $storno_dl . 'D'))->format('Y-m-d') < date('Y-m-d');
    }

    protected function memberRole($role_id)
    {
        return strpos(\ilObject::_lookupTitle($role_id), 'il_crs_member_') === 0;
    }
}
