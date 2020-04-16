<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WBDCrsHistorizing\HistCases;

use PHPUnit\Framework\TestCase;
use CaT\Historization\Event\Event;

class WBDCrsTestObject extends WBDCrs
{
    protected function relevantRole($role_id)
    {
        if ($role_id === 22) {
            return true;
        }
        return false;
    }
}

class WBDCrsTest extends TestCase
{
    const CRS_ID = 'crs_id';
    const CREATED_TS = 'created_ts';
    const CREATOR = 'creator';
    const INTERNAL_ID = 'internal_id';
    const WBD_LEARNING_TYPE = 'wbd_learning_type';
    const WBD_LEARNING_CONTENT = 'wbd_learning_content';
    const CONTACT_TITLE_TUTOR = 'contact_title_tutor';
    const CONTACT_FIRSTNAME_TUTOR = 'contact_firstname_tutor';
    const CONTACT_LASTNAME_TUTOR = 'contact_lastname_tutor';
    const CONTACT_EMAIL_TUTOR = 'contact_email_tutor';
    const CONTACT_PHONE_TUTOR = 'contact_phone_tutor';
    const CONTACT_TITLE_ADMIN = 'contact_title_admin';
    const CONTACT_FIRSTNAME_ADMIN = 'contact_firstname_admin';
    const CONTACT_LASTNAME_ADMIN = 'contact_lastname_admin';
    const CONTACT_EMAIL_ADMIN = 'contact_email_admin';
    const CONTACT_PHONE_ADMIN = 'contact_phone_admin';
    const CONTACT_TITLE_XCCL = 'contact_title_xccl';
    const CONTACT_FIRSTNAME_XCCL = 'contact_firstname_xccl';
    const CONTACT_LASTNAME_XCCL = 'contact_lastname_xccl';
    const CONTACT_EMAIL_XCCL = 'contact_email_xccl';
    const CONTACT_PHONE_XCCL = 'contact_phone_xccl';

    const HIST_TYPE_TEXT = 'hist_type_text';
    const HIST_TYPE_INT = 'hist_type_int';
    const HIST_TYPE_TIMESTAMP = 'hist_type_timestamp';
    const HIST_TYPE_BOOL = 'hist_type_bool';

    private static $fields = [
        self::CRS_ID,
        self::CREATED_TS,
        self::CREATOR,
        self::INTERNAL_ID,
        self::WBD_LEARNING_TYPE,
        self::WBD_LEARNING_CONTENT,
        self::CONTACT_TITLE_TUTOR,
        self::CONTACT_FIRSTNAME_TUTOR,
        self::CONTACT_LASTNAME_TUTOR,
        self::CONTACT_EMAIL_TUTOR,
        self::CONTACT_PHONE_TUTOR,
        self::CONTACT_TITLE_ADMIN,
        self::CONTACT_FIRSTNAME_ADMIN,
        self::CONTACT_LASTNAME_ADMIN,
        self::CONTACT_EMAIL_ADMIN,
        self::CONTACT_PHONE_ADMIN,
        self::CONTACT_TITLE_XCCL,
        self::CONTACT_FIRSTNAME_XCCL,
        self::CONTACT_LASTNAME_XCCL,
        self::CONTACT_EMAIL_XCCL,
        self::CONTACT_PHONE_XCCL
    ];

    private static $types = [
        self::HIST_TYPE_BOOL,
        self::HIST_TYPE_INT,
        self::HIST_TYPE_TEXT,
        self::HIST_TYPE_TIMESTAMP,
    ];

    protected $obj;

    public function setUp() : void
    {
        $this->obj = new WBDCrsTestObject();
    }

    public function testTitle() : void
    {
        $this->assertEquals('wbd_crs', $this->obj->title());
    }

    public function testId() : void
    {
        $result = $this->obj->id();

        $this->assertIsArray($result);
        $this->assertEquals('crs_id', array_shift($result));
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

    public function test_isEventRelevant()
    {
        $events = $this->getRelevantEvents();

        foreach ($events as $event) {
            $this->assertTrue($this->obj->isEventRelevant($event));
        }
    }

    protected function getRelevantEvents() : array
    {
        $events = [
            new Event('assignUser', 'Services/AccessControl', ['type' => 'crs', 'role_id' => 22]),
            new Event('deassignUser', 'Services/AccessControl', ['type' => 'crs', 'role_id' => 22]),
            new Event('assignUser', 'Services/AccessControl', ['type' => 'crs', 'role_id' => 22]),
            new Event('updateCCObject', 'Plugin/CourseClassification', []),
            new Event('updateWBD', 'Plugin/EduTracking', []),
            new Event('importCourseWBD', 'Plugin/WBDInterface', [])
        ];

        return $events;
    }

    public function test_isEventNotRelevant()
    {
        $events = $this->getNotRelevantEvents();

        foreach ($events as $event) {
            $this->assertFalse($this->obj->isEventRelevant($event));
        }
    }

    protected function getNotRelevantEvents() : array
    {
        $crs = $this->createMock(\ilObjCourse::class);

        $events = [
            new Event('assignUser', 'Services/AccessControl', ['type' => 'crs', 'role_id' => 33]),
            new Event('assignUser', 'Services/AccessControl', ['type' => 'xwbd', 'role_id' => 22]),
            new Event('move', 'Plugin/CourseClassification', []),
            new Event('move', 'Plugin/WBDInterface', []),
            new Event('move', 'Plugin/EduTracking', [])
        ];

        return $events;
    }

    public function testIsBuffered() : void
    {
        $this->assertTrue($this->obj->isBuffered());
    }

    public function testCreatorField() : void
    {
        $this->assertEquals('creator', $this->obj->creatorField());
    }

    public function testTimestampField() : void
    {
        $this->assertEquals('created_ts', $this->obj->timestampField());
    }

    public function testCaseIdComplete() : void
    {
        $payload = [
            'crs_id' => 'dummy'
        ];

        $this->assertTrue($this->obj->caseIdComplete($payload));
    }

    public function testNoValueEntryForFieldType() : void
    {
        $this->assertNull($this->obj->noValueEntryForFieldType(''));
    }
}
