<?php

namespace CaT\Plugins\TrainerOperations\Settings;

use PHPUnit\Framework\TestCase;

class SettingsTest extends TestCase
{
    public function setUp() : void
    {
        $this->obj_id = 1;
        $this->roles = [1,2,3];
        $this->settings = new Settings($this->obj_id, $this->roles);
    }

    public function test_construct()
    {
        $this->assertInstanceOf(Settings::class, $this->settings);
        $this->assertEquals($this->obj_id, $this->settings->getObjId());
        $this->assertEquals($this->roles, $this->settings->getGlobalRoles());
    }

    public function test_withRoles()
    {
        $nu_roles = [4,5,6];
        $settings = $this->settings->withGlobalRoles($nu_roles);
        $this->assertInstanceOf(Settings::class, $settings);
        $this->assertEquals($nu_roles, $settings->getGlobalRoles());
    }
}
