<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\TrainingSearch\Settings;

use PHPUnit\Framework\TestCase;

class SettingsTest extends TestCase
{
    public function testCreateEmpty()
    {
        $settings = new Settings(1, false, true);
        $this->assertEquals(1, $settings->getObjId());
        $this->assertFalse($settings->getIsOnline());
        $this->assertTrue($settings->isLocal());
        $this->assertEmpty($settings->relevantTopics());
        $this->assertEmpty($settings->relevantCategories());
        $this->assertEmpty($settings->relevantTargetGroups());
    }

    public function testSetOnline()
    {
        $settings = new Settings(1, false);
        $online_settings = $settings->withIsOnline(true);

        $this->assertEquals(1, $settings->getObjId());
        $this->assertFalse($settings->getIsOnline());

        $this->assertEquals(1, $online_settings->getObjId());
        $this->assertTrue($online_settings->getIsOnline());
    }

    public function testSetLocal()
    {
        $settings = new Settings(1, false, false);
        $online_settings = $settings->withIsLocal(true);

        $this->assertEquals(1, $settings->getObjId());
        $this->assertFalse($settings->getIsOnline());
        $this->assertFalse($settings->isLocal());

        $this->assertEquals(1, $online_settings->getObjId());
        $this->assertFalse($online_settings->getIsOnline());
        $this->assertTrue($online_settings->isLocal());
    }

    public function testSetTopics()
    {
        $settings = new Settings(1, false, false);
        $this->assertEquals(1, $settings->getObjId());
        $this->assertFalse($settings->getIsOnline());
        $this->assertFalse($settings->isLocal());
        $this->assertEmpty($settings->relevantTopics());
        $this->assertEmpty($settings->relevantCategories());
        $this->assertEmpty($settings->relevantTargetGroups());
        $settings = $settings->withRelevantTopics([1,2,3]);
        $this->assertEquals(1, $settings->getObjId());
        $this->assertFalse($settings->getIsOnline());
        $this->assertFalse($settings->isLocal());
        $this->assertEquals([1,2,3], $settings->relevantTopics());
        $this->assertEmpty($settings->relevantCategories());
        $this->assertEmpty($settings->relevantTargetGroups());
    }

    public function testSetCategories()
    {
        $settings = new Settings(1, false, false);
        $this->assertEquals(1, $settings->getObjId());
        $this->assertFalse($settings->getIsOnline());
        $this->assertFalse($settings->isLocal());
        $this->assertEmpty($settings->relevantTopics());
        $this->assertEmpty($settings->relevantCategories());
        $this->assertEmpty($settings->relevantTargetGroups());

        $settings = $settings->withRelevantCategories([4,5,6]);
        $this->assertEquals(1, $settings->getObjId());
        $this->assertFalse($settings->getIsOnline());
        $this->assertFalse($settings->isLocal());
        $this->assertEmpty($settings->relevantTopics());
        $this->assertEquals([4,5,6], $settings->relevantCategories());
        $this->assertEmpty($settings->relevantTargetGroups());
    }

    public function testSetTargetGroups()
    {
        $settings = new Settings(1, false, false);
        $this->assertEquals(1, $settings->getObjId());
        $this->assertFalse($settings->getIsOnline());
        $this->assertFalse($settings->isLocal());
        $this->assertEmpty($settings->relevantTopics());
        $this->assertEmpty($settings->relevantCategories());
        $this->assertEmpty($settings->relevantTargetGroups());

        $settings = $settings->withRelevantTargetGroups([4,5,6]);
        $this->assertEquals(1, $settings->getObjId());
        $this->assertFalse($settings->getIsOnline());
        $this->assertFalse($settings->isLocal());
        $this->assertEmpty($settings->relevantTopics());
        $this->assertEmpty($settings->relevantCategories());
        $this->assertEquals([4,5,6], $settings->relevantTargetGroups());
    }

    public function testSetRecommendation()
    {
        $settings = new Settings(1, false);
        $this->assertFalse($settings->isRecommendationAllowed());
        $settings = $settings->withIsRecommendationAllowed(true);
        $this->assertTrue($settings->isRecommendationAllowed());
    }
}
