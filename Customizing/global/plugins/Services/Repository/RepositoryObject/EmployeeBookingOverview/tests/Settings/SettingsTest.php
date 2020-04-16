<?php

namespace CaT\Plugins\EmployeeBookingOverview\Settings;

use PHPUnit\Framework\TestCase;

/**
 * Sample for PHP Unit tests
 */
class SettingsTest extends TestCase
{
    public function test_init()
    {
        $s = new Settings(1);
        $this->assertEquals(1, $s->objId());
        $this->assertEquals(false, $s->isOnline());
        $this->assertEquals(false, $s->isGlobal());
        $this->assertEquals([], $s->getInvisibleCourseTopics());


        $s = new Settings(1, true, true, ["Test"]);
        $this->assertEquals(1, $s->objId());
        $this->assertEquals(true, $s->isOnline());
        $this->assertEquals(true, $s->isGlobal());
        $this->assertEquals(["Test"], $s->getInvisibleCourseTopics());
    }

    public function test_with_online()
    {
        $s = new Settings(2);
        $this->assertEquals(true, $s->withOnline(true)->isOnline());
        $this->assertEquals(false, $s->withOnline(false)->isOnline());
    }

    public function test_with_global()
    {
        $s = new Settings(2);
        $this->assertEquals(true, $s->withGlobal(true)->isGlobal());
        $this->assertEquals(false, $s->withGlobal(false)->isGlobal());
    }

    public function test_with_invisibl_course_topics()
    {
        $s = new Settings(2);
        $this->assertEquals(
            ["Test"],
            $s->withInvisibleCourseTopics(["Test"])->getInvisibleCourseTopics()
        );
        $this->assertEquals([], $s->withInvisibleCourseTopics([])->getInvisibleCourseTopics());
    }
}
