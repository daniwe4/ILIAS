<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\TrainingSearch\Settings;

use PHPUnit\Framework\TestCase;

class DBTest extends TestCase
{
    public function testCreateObject()
    {
        $db = $this->createMock(\ilDBInterface::class);

        $settings_db = new ilDB($db);
        $this->assertInstanceOf(ilDB::class, $settings_db);
    }

    public function testCreateSettings()
    {
        $settings = new Settings(1, false);
        $db = $this->createMock(\ilDBInterface::class);

        $values = [
            "obj_id" => ["integer", 1],
            "is_online" => ["integer", false],
            "is_local" => ["integer", false],
            "is_recommendation_allowed" => ["integer", false]
        ];

        $db
            ->expects($this->once())
            ->method("insert")
            ->with("xtrs_settings", $values);

        $settings_db = new ilDB($db);
        $n_settings = $settings_db->create(1);
        $this->assertEquals($settings, $n_settings);
        $this->assertEquals(1, $n_settings->getObjId());
        $this->assertFalse($n_settings->getIsOnline());
    }

    public function testSelectSettings()
    {
        $settings = new Settings(1, false);
        $db = $this->createMock(\ilDBInterface::class);

        $res = [
            [
                "obj_id" => 1,
                "is_online" => false,
                "is_local" => false,
                "is_recommendation_allowed" => false
            ]
        ];
        $row = [
            "obj_id" => 1,
            "is_online" => false,
            "is_local" => false,
            "is_recommendation_allowed" => false
        ];

        $db
            ->expects($this->exactly(4))
            ->method("quote")
            ->with(1)
            ->willReturn(1);

        $db
            ->expects($this->exactly(4))
            ->method("query")
            ->willReturn($res);

        $db
            ->expects($this->once())
            ->method("numRows")
            ->willReturn(1);

        $db
            ->expects($this->exactly(4))
            ->method("fetchAssoc")
            ->will($this->onConsecutiveCalls($row, false, false));

        $settings_db = new ilDB($db);
        $n_settings = $settings_db->select(1);
        $this->assertEquals($settings, $n_settings);
        $this->assertEquals(1, $n_settings->getObjId());
        $this->assertFalse($n_settings->getIsOnline());
        $this->assertCount(0, $n_settings->relevantTopics());
        $this->assertCount(0, $n_settings->relevantCategories());
    }

    public function testSelectSettingsNoDBEntry()
    {
        $settings = new Settings(1, false);
        $db = $this->createMock(\ilDBInterface::class);

        $res = [];

        $values = [
            "obj_id" => ["integer", 1],
            "is_online" => ["integer", false],
            "is_local" => ["integer", false],
            "is_recommendation_allowed" => ["integer", false]
        ];

        $db
            ->expects($this->once())
            ->method("quote")
            ->with(1)
            ->willReturn(1);

        $db
            ->expects($this->once())
            ->method("query")
            ->willReturn($res);

        $db
            ->expects($this->once())
            ->method("numRows")
            ->with($res)
            ->willReturn(0);

        $db
            ->expects($this->once())
            ->method("insert")
            ->with("xtrs_settings", $values);

        $db
            ->expects($this->never())
            ->method("fetchAssoc");

        $settings_db = new ilDB($db);
        $n_settings = $settings_db->select(1);
        $this->assertEquals($settings, $n_settings);
        $this->assertEquals(1, $n_settings->getObjId());
        $this->assertFalse($n_settings->getIsOnline());
    }

    public function testUpdateSettings()
    {
        $settings = new Settings(1, false, false);
        $db = $this->createMock(\ilDBInterface::class);

        $where = [
            "obj_id" => ["integer", 1]
        ];

        $values = [
            "is_online" => ["integer", false],
            "is_local" => ["integer", false],
            "is_recommendation_allowed" => ["integer", false],
        ];

        $db
            ->expects($this->once())
            ->method("update")
            ->with("xtrs_settings", $values, $where)
            ->willReturn(null);

        $settings_db = new ilDB($db);
        $settings_db->update($settings);
    }

    public function testDeleteSettings()
    {
        $db = $this->createMock(\ilDBInterface::class);

        $db
            ->expects($this->exactly(4))
            ->method("quote")
            ->with(1)
            ->willReturn(1);

        $db
            ->expects($this->exactly(4))
            ->method("manipulate")
            ->willReturn(null);

        $settings_db = new ilDB($db);
        $settings_db->delete(1);
    }
}
