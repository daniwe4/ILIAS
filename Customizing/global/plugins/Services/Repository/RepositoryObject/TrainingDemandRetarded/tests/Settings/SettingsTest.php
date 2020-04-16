<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainingDemandRetarded\Settings;

use PHPUnit\Framework\TestCase;

class SettingsTest extends TestCase
{
    public function test_create_instance()
    {
        $id = 10;
        $online = false;
        $global = true;
        $local_roles = [];

        $settings = new Settings(
            $id,
            $online,
            $global,
            $local_roles
        );

        $this->assertInstanceof(Settings::class, $settings);
        $this->assertEquals($id, $settings->id());
        $this->assertFalse($settings->online());
        $this->assertTrue($settings->isGlobal());
        $this->assertEquals($local_roles, $settings->getLocalRoles());
    }

    public function test_with_online()
    {
        $id = 10;
        $online = false;
        $global = true;
        $local_roles = [];

        $settings = new Settings(
            $id,
            $online,
            $global,
            $local_roles
        );

        $n_settings = $settings->withOnline(true);

        $this->assertEquals($id, $settings->id());
        $this->assertFalse($settings->online());
        $this->assertTrue($settings->isGlobal());
        $this->assertEquals($local_roles, $settings->getLocalRoles());

        $this->assertEquals($id, $n_settings->id());
        $this->assertTrue($n_settings->online());
        $this->assertTrue($n_settings->isGlobal());
        $this->assertEquals($local_roles, $n_settings->getLocalRoles());

        $this->assertNotSame($settings, $n_settings);
    }

    public function test_with_global()
    {
        $id = 10;
        $online = false;
        $global = true;
        $local_roles = [];

        $settings = new Settings(
            $id,
            $online,
            $global,
            $local_roles
        );

        $n_settings = $settings->withGlobal(false);

        $this->assertEquals($id, $settings->id());
        $this->assertFalse($settings->online());
        $this->assertTrue($settings->isGlobal());
        $this->assertEquals($local_roles, $settings->getLocalRoles());

        $this->assertEquals($id, $n_settings->id());
        $this->assertFalse($n_settings->online());
        $this->assertFalse($n_settings->isGlobal());
        $this->assertEquals($local_roles, $n_settings->getLocalRoles());

        $this->assertNotSame($settings, $n_settings);
    }

    public function test_with_local_roles()
    {
        $id = 10;
        $online = false;
        $global = true;
        $local_roles = [];
        $n_local_roles = ["admin", "member"];

        $settings = new Settings(
            $id,
            $online,
            $global,
            $local_roles
        );

        $n_settings = $settings->withLocalRoles($n_local_roles);

        $this->assertEquals($id, $settings->id());
        $this->assertFalse($settings->online());
        $this->assertTrue($settings->isGlobal());
        $this->assertEquals($local_roles, $settings->getLocalRoles());

        $this->assertEquals($id, $n_settings->id());
        $this->assertFalse($n_settings->online());
        $this->assertTrue($n_settings->isGlobal());
        $this->assertEquals($n_local_roles, $n_settings->getLocalRoles());

        $this->assertNotSame($settings, $n_settings);
    }
}
