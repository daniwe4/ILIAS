<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainingStatisticsByOrgUnits\Settings;

use PHPUnit\Framework\TestCase;

/**
* Sample for PHP Unit tests
*/
class SettingsTest extends TestCase
{
    public function test_create_object()
    {
        $obj = new Settings(1, false, false);
        $this->assertInstanceOf(Settings::class, $obj);
        $this->assertEquals(1, $obj->getId());
        $this->assertFalse($obj->isOnline());
    }

    public function test_with_online()
    {
        $obj = new Settings(1, false, false);
        $c_obj = $obj->withIsOnline(true);
        $this->assertInstanceOf(Settings::class, $obj);
        $this->assertEquals(1, $obj->getId());
        $this->assertFalse($obj->isOnline());

        $this->assertInstanceOf(Settings::class, $c_obj);
        $this->assertEquals(1, $c_obj->getId());
        $this->assertTrue($c_obj->isOnline());

        $this->assertNotSame($obj, $c_obj);
    }
}
