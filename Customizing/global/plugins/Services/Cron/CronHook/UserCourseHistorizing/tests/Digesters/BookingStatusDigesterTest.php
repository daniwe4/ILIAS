<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use PHPUnit\Framework\TestCase;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistUserCourse;

class TestObject extends BookingStatusDigester
{
    protected function cancellationAfterDeadline($id)
    {
        if (0 == $id) {
            return false;
        }
        return true;
    }

    protected function memberRole($id)
    {
        if (0 == $id) {
            return false;
        }
        return true;
    }
}

class BookingStatusDigesterTest extends TestCase
{
    const E_ASSIGN_USER = 'assignUser';
    const E_DEASSIGN_USER = 'deassignUser';
    const E_ADD_TO_WAITING_LIST = 'addToWaitingList';
    const E_USER_CANCELED_SELF_FROM_WAITING = 'user_canceled_self_from_waiting';
    const E_SUPERIOR_CANCELED_USER_FROM_WAITING = 'superior_canceled_user_from_waiting';
    const E_REQUEST_CREATED = 'request_created';
    const E_REQUEST_DECLINED = 'request_declined';
    const E_REQUEST_APPROVED = 'request_approved';
    const E_REQUEST_REVOKED = 'request_revoked';

    private static $events = [
      self::E_ASSIGN_USER,
      self::E_DEASSIGN_USER,
      self::E_ADD_TO_WAITING_LIST,
      self::E_USER_CANCELED_SELF_FROM_WAITING,
      self::E_SUPERIOR_CANCELED_USER_FROM_WAITING,
      self::E_REQUEST_CREATED,
      self::E_REQUEST_DECLINED,
      self::E_REQUEST_APPROVED,
      self::E_REQUEST_REVOKED
    ];

    /**
     * @var Mocks
     */
    protected $mocks;

    public function setUp() : void
    {
        $this->mocks = new Mocks();
    }

    public function testDigest() : void
    {
        $date = date('Y-m-d');

        foreach (self::$events as $event) {
            $payload = [
                'usr_id' => 10,
                'obj_id' => 20,
                'role_id' => 1
            ];

            $obj = new TestObject($event);
            $result = $obj->digest($payload);

            switch ($event) {
                case self::E_ASSIGN_USER:
                    $this->assertEquals(
                        HistUserCourse::BOOKING_STATUS_PARTICIPANT,
                        $result['booking_status']
                    );
                    $this->assertEquals($date, $result['booking_date']);
                    break;
                case self::E_DEASSIGN_USER:
                    $this->assertEquals(
                        HistUserCourse::BOOKING_STATUS_CANCELLED_AFTER_DEADLINE,
                        $result['booking_status']
                    );
                    $this->assertEquals($date, $result['cancel_booking_date']);
                    break;
                case self::E_ADD_TO_WAITING_LIST:
                    $this->assertEquals(
                        HistUserCourse::BOOKING_STATUS_WAITING,
                        $result['booking_status']
                    );
                    $this->assertEquals($date, $result['waiting_date']);
                    break;
                case self::E_REQUEST_APPROVED:
                    $this->assertEquals(
                        HistUserCourse::BOOKING_STATUS_PARTICIPANT,
                        $result['booking_status']
                    );
                    break;
                case self::E_REQUEST_CREATED:
                    $this->assertEquals(
                        HistUserCourse::APPROVAL_REQUEST_CREATED,
                        $result['booking_status']
                    );
                    break;
                case self::E_REQUEST_DECLINED:
                    $this->assertEquals(
                        HistUserCourse::APPROVAL_REQUEST_DECLINED,
                        $result['booking_status']
                    );
                    break;
                case self::E_REQUEST_REVOKED:
                    $this->assertEquals(
                        HistUserCourse::APPROVAL_REQUEST_REVOKED,
                        $result['booking_status']
                    );
                    break;
                case self::E_SUPERIOR_CANCELED_USER_FROM_WAITING:
                case self::E_USER_CANCELED_SELF_FROM_WAITING:
                $this->assertEquals(
                    HistUserCourse::BOOKING_STATUS_WAITING_SELF_CANCELLED,
                    $result['booking_status']
                );
                $this->assertEquals($date, $result['cancel_waiting_date']);
                break;
            }

            $payload['obj_id'] = 0;
            $result = $obj->digest($payload);

            switch ($event) {
                case self::E_DEASSIGN_USER:
                    $this->assertEquals(
                        HistUserCourse::BOOKING_STATUS_CANCELLED,
                        $result['booking_status']
                    );
                    $this->assertEquals($date, $result['cancel_booking_date']);
                    break;
            }

            $payload['obj_id'] = 22;
            $payload['role_id'] = 0;
            $result = $obj->digest($payload);

            switch ($event) {
                case self::E_ASSIGN_USER:
                    $this->assertNull($result['booking_status']);
                    $this->assertNull($result['booking_date']);
                    break;
                case self::E_DEASSIGN_USER:
                    $this->assertNull($result['booking_status']);
                    $this->assertNull($result['cancel_booking_date']);
                    break;
            }
        }
    }
}
