<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\UserCourseHistorizing\HistCases;

use PHPUnit\Framework\TestCase;

class HistUserCourseTestObject extends HistUserCourse
{
    protected function isCourse($id)
    {
        if ($id === 22) {
            return true;
        }
        return false;
    }

    protected function shouldBeSkipped($id) : bool
    {
        if ($id === 22) {
            return false;
        }
        return true;
    }
}

class HistUserCourseTest extends TestCase
{
    const CRS_ID = 'crs_id';
    const USR_ID = 'usr_id';
    const BOOKING_STATUS = 'booking_status';
    const PARTICIPATION_STATUS = 'participation_status';
    const CUSTOM_P_STATUS = 'custom_p_status';
    const CREATED_TS = 'created_ts';
    const CREATOR = 'creator';
    const NIGHTS = 'nights';
    const BOOKING_DATE = 'booking_date';
    const PS_AQUIRED_DATE = 'ps_acquired_date';
    const IDD_LEARNING_TIME = 'idd_learning_time';
    const PRIOR_NIGHT = 'prior_night';
    const FOLLOWING_NIGHT = 'following_night';
    const CANCEL_BOOKING_DATE = 'cancel_booking_date';
    const WAITING_DATE = 'waiting_date';
    const CANCEL_WAITING_DATE = 'cancel_waiting_date';
    const WBD_BOOKING_ID = 'wbd_booking_id';
    const CANCELLATION_FEE = 'cancellation_fee';
    const ROLES = 'roles';

    const HIST_TYPE_WORD = 'hist_type_word';
    const HIST_TYPE_STRING = 'hist_type_string';
    const HIST_TYPE_TEXT = 'hist_type_text';
    const HIST_TYPE_INT = 'hist_type_int';
    const HIST_TYPE_DATE = 'hist_type_date';
    const HIST_TYPE_DATE_TIME = 'hist_type_date_time';
    const HIST_TYPE_TIMESTAMP = 'hist_type_timestamp';
    const HIST_TYPE_LIST_INT = 'hist_type_list_int';
    const HIST_TYPE_LIST_STRING = 'hist_type_list_string';
    const HIST_TYPE_BOOL = 'hist_type_bool';
    const HIST_TYPE_FLOAT = 'hist_type_float';

    private static $fields = [
        self::CRS_ID,
        self::USR_ID,
        self::BOOKING_STATUS,
        self::PARTICIPATION_STATUS,
        self::CUSTOM_P_STATUS,
        self::CREATED_TS,
        self::CREATOR,
        self::NIGHTS,
        self::BOOKING_DATE,
        self::PS_AQUIRED_DATE,
        self::IDD_LEARNING_TIME,
        self::PRIOR_NIGHT,
        self::FOLLOWING_NIGHT,
        self::CANCEL_BOOKING_DATE,
        self::WAITING_DATE,
        self::CANCEL_WAITING_DATE,
        self::WBD_BOOKING_ID,
        self::CANCELLATION_FEE,
        self::ROLES
    ];

    private static $types = [
        self::HIST_TYPE_BOOL,
        self::HIST_TYPE_DATE,
        self::HIST_TYPE_DATE_TIME,
        self::HIST_TYPE_FLOAT,
        self::HIST_TYPE_INT,
        self::HIST_TYPE_LIST_INT,
        self::HIST_TYPE_LIST_STRING,
        self::HIST_TYPE_TEXT,
        self::HIST_TYPE_TIMESTAMP,
        self::HIST_TYPE_WORD,
        self::HIST_TYPE_STRING
    ];

    /**
     * @var HistUserCourseTestObject
     */
    protected $obj;

    public function setUp() : void
    {
        $this->obj = new HistUserCourseTestObject();
    }

    public function testTitle()
    {
        $this->assertEquals('usrcrs', $this->obj->title());
    }

    public function testId()
    {
        $ids = $this->obj->id();

        $this->assertIsArray($ids);

        $this->assertTrue(in_array(self::USR_ID, $ids));
        $this->assertTrue(in_array(self::CRS_ID, $ids));
    }

    public function test_fields()
    {
        $fields = $this->obj->fields();

        foreach ($fields as $field) {
            if (!in_array($field, self::$fields)) {
                $this->assertTrue(false);
            }
            $this->assertTrue(true);
        }
    }

    public function test_payloadFields()
    {
        $payload_fields = $this->obj->payloadFields();

        foreach ($payload_fields as $payload_field) {
            if (!in_array($payload_field, self::$fields)) {
                $this->assertTrue(false);
            }
            $this->assertTrue(true);
        }
    }

    public function test_typeOfField()
    {
        foreach (self::$fields as $field) {
            $type = $this->obj->typeOfField($field);
            if (!in_array($type, self::$types)) {
                $this->assertTrue(false);
            }
            $this->assertTrue(true);
        }
    }
}
