<?php

use PHPUnit\Framework\TestCase;
use CaT\Plugins\CancellationFeeReport\Settings\Settings as Settings;

/**
 * Sample for PHP Unit tests
 */
class SettingsTest extends TestCase
{
    protected $backupGlobals = false;

    public function test_init_default()
    {
        $set = new Settings(1);
        $this->assertEquals(1, $set->id());
        $this->assertFalse($set->isGlobal());
        $this->assertFalse($set->online());
    }

    public function initProvider()
    {
        return [
            [1,true,true],
            [2,false,false],
            [3,true,false],
            [4,false,true]
        ];
    }

    /**
     * @dataProvider initProvider
     */
    public function test_init_params($id, $online, $global)
    {
        $set = new Settings($id, $online, $global);
        $this->assertEquals($set->id(), $id);
        $this->assertEquals($set->isGlobal(), $global);
        $this->assertEquals($set->online(), $online);
    }
}
