<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\UserCourseHistorizing\HistCases;

use PHPUnit\Framework\TestCase;

class TestObject extends HistSessionCourse
{
    protected function inCourse($ref_id)
    {
        if ($ref_id === 22) {
            return true;
        }
        return false;
    }
}

class HistSessionCourseTest extends TestCase
{
    const SESSION_ID = 'session_id';
    const CRS_ID = 'crs_id';
    const BEGIN_DATE = 'begin_date';
    const END_DATE = 'end_date';
    const START_TIME = 'start_time';
    const END_TIME = 'end_time';
    const CREATED_TS = 'created_ts';
    const CREATOR = 'creator';
    const FULLDAY = 'fullday';
    const REMOVED = 'removed';

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
        self::SESSION_ID,
        self::CRS_ID,
        self::BEGIN_DATE,
        self::END_DATE,
        self::START_TIME,
        self::END_TIME,
        self::CREATED_TS,
        self::CREATOR,
        self::FULLDAY,
        self::REMOVED
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
     * @var HistSessionCourse
     */
    protected $obj;

    public function setUp() : void
    {
        $this->obj = new TestObject();
    }

    public function testTitle()
    {
        $this->assertEquals('sesscrs', $this->obj->title());
    }

    public function testId()
    {
        $ids = $this->obj->id();

        $this->assertIsArray($ids);

        $this->assertTrue(in_array(self::SESSION_ID, $ids));
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
