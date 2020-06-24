<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\UserCourseHistorizing\HistCases;

use PHPUnit\Framework\TestCase;

class HistCourseTest extends TestCase
{
    const CRS_ID = 'crs_id';
    const TITLE = 'title';
    const CRS_TYPE = 'crs_type';
    const TOPICS = 'topics';
    const DELETED = 'deleted';
    const VENUE = 'venue';
    const ACCOMODATION = 'accomodation';
    const TUT = 'tut';
    const PROVIDER = 'provider';
    const BEGIN_DATE = 'begin_date';
    const END_DATE = 'end_date';
    const CREATED_TS = 'created_ts';
    const CREATOR = 'creator';
    const EDU_PROGRAMME = 'edu_programme';
    const CATEGORIES = 'categories';
    const IDD_LEARNING_TIME = 'idd_learning_time';
    const IS_TEMPLATE = 'is_template';
    const BOOKING_DL_DATE = 'booking_dl_date';
    const STORNO_DL_DATE = 'storno_dl_date';
    const BOOKING_DL = 'booking_dl';
    const STORNO_DL = 'storno_dl';
    const MAX_MEMBERS = 'max_members';
    const MIN_MEMBERS = 'min_members';
    const NET_TOTAL_COST = 'net_total_cost';
    const GROSS_TOTAL_COST = 'gross_total_cost';
    const COSTCENTER_FINALIZED = 'costcenter_finalized';
    const PARTICIPATION_FINALIZED_DATE = 'participation_finalized_date';
    const ACCOMODATION_DATE_START = 'accomodation_date_start';
    const ACCOMODATION_DATE_END = 'accomodation_date_end';
    const FEE = 'fee';
    const TO_BE_ACKNOWLEDGED = 'to_be_acknowledged';
    const VENUE_FREETEXT = 'venue_freetext';
    const PROVIDER_FREETEXT = 'provider_freetext';
    const GTI_LEARNING_TIME = 'gti_learning_time';
    const MAX_CANCELLATION_FEE = 'max_cancellation_fee';
    const GTI_CATEGORY = 'gti_category';
    const TARGET_GROUPS = 'target_groups';
    const VENUE_FROM_COURSE = 'venue_from_course';

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
        self::TITLE,
        self::CRS_TYPE,
        self::TOPICS,
        self::DELETED,
        self::VENUE,
        self::ACCOMODATION,
        self::TUT,
        self::PROVIDER,
        self::BEGIN_DATE,
        self::END_DATE,
        self::CREATED_TS,
        self::CREATOR,
        self::EDU_PROGRAMME,
        self::CATEGORIES,
        self::IDD_LEARNING_TIME,
        self::IS_TEMPLATE,
        self::BOOKING_DL_DATE,
        self::STORNO_DL_DATE,
        self::BOOKING_DL,
        self::STORNO_DL,
        self::MAX_MEMBERS,
        self::MIN_MEMBERS,
        self::NET_TOTAL_COST,
        self::GROSS_TOTAL_COST,
        self::COSTCENTER_FINALIZED,
        self::PARTICIPATION_FINALIZED_DATE,
        self::ACCOMODATION_DATE_START,
        self::ACCOMODATION_DATE_END,
        self::FEE,
        self::TO_BE_ACKNOWLEDGED,
        self::VENUE_FREETEXT,
        self::PROVIDER_FREETEXT,
        self::GTI_LEARNING_TIME,
        self::MAX_CANCELLATION_FEE,
        self::GTI_CATEGORY,
        self::TARGET_GROUPS,
        self::VENUE_FROM_COURSE
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
     * @var HistCourse
     */
    protected $obj;

    public function setUp() : void
    {
        $this->obj = new HistCourse();
    }

    public function test_IsCourse()
    {
        $this->assertEquals('crs', $this->obj->title());
    }

    public function test_Id()
    {
        $id = $this->obj->id();
        $this->assertIsArray($id);
        $this->assertEquals('crs_id', array_shift($id));
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
