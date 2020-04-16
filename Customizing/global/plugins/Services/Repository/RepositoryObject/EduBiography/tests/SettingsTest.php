<?php

use CaT\Plugins\EduBiography\Settings as Settings;
use PHPUnit\Framework\TestCase;

class SettingsTest extends TestCase
{
    public function test_init()
    {
        $settings = new Settings\Settings(1, false, false, [], []);
        $this->assertInstanceOf(Settings\Settings::class, $settings);
        return $settings;
    }

    /**
     * @depends test_init
     */
    public function test_getter($set)
    {
        $this->assertEquals($set->id(), 1);
        $this->assertFalse($set->isOnline());
        $this->assertFalse($set->hasSuperiorOverview());
        return $set;
    }

    /**
     * @depends test_getter
     */
    public function test_with_online($set)
    {
        $this->assertTrue($set->withIsOnline(true)->isOnline());
    }

    /**
     * @depends test_getter
     */
    public function test_with_hasSuperiorOverview($set)
    {
        $this->assertTrue($set->withHasSuperiorOverview(true)->hasSuperiorOverview());
    }
}
