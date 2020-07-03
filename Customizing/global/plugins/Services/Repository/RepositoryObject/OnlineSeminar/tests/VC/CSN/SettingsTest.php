<?php

namespace CaT\Plugins\OnlineSeminar\VC\CSN;

use PHPUnit\Framework\TestCase;

class SettingsTest extends TestCase
{
    public function test_create()
    {
        $settings = new Settings(1, "0123456789", "pin", 25);

        $this->assertEquals(1, $settings->getObjId());
        $this->assertEquals("0123456789", $settings->getPhone());
        $this->assertEquals("pin", $settings->getPin());
        $this->assertEquals(25, $settings->getMinutesRequired());
        $this->assertFalse($settings->isUploadRequired());

        return $settings;
    }

    /**
     * @depends test_create
     */
    public function test_withPhone($settings)
    {
        $new_settings = $settings->withPhone("9876543210");

        $this->assertEquals(1, $settings->getObjId());
        $this->assertEquals("0123456789", $settings->getPhone());
        $this->assertEquals("pin", $settings->getPin());
        $this->assertEquals(25, $settings->getMinutesRequired());
        $this->assertFalse($settings->isUploadRequired());

        $this->assertEquals(1, $new_settings->getObjId());
        $this->assertEquals("9876543210", $new_settings->getPhone());
        $this->assertEquals("pin", $new_settings->getPin());
        $this->assertEquals(25, $new_settings->getMinutesRequired());
        $this->assertFalse($new_settings->isUploadRequired());

        $this->assertNotSame($new_settings, $settings);

        return $settings;
    }

    /**
     * @depends test_withPhone
     */
    public function test_withPin($settings)
    {
        $new_settings = $settings->withPin("new_pin");

        $this->assertEquals(1, $settings->getObjId());
        $this->assertEquals("0123456789", $settings->getPhone());
        $this->assertEquals("pin", $settings->getPin());
        $this->assertEquals(25, $settings->getMinutesRequired());
        $this->assertFalse($settings->isUploadRequired());

        $this->assertEquals(1, $new_settings->getObjId());
        $this->assertEquals("0123456789", $new_settings->getPhone());
        $this->assertEquals("new_pin", $new_settings->getPin());
        $this->assertEquals(25, $new_settings->getMinutesRequired());
        $this->assertFalse($new_settings->isUploadRequired());

        $this->assertNotSame($new_settings, $settings);

        return $settings;
    }

    /**
     * @depends test_withPin
     */
    public function test_withMinutesRequired($settings)
    {
        $new_settings = $settings->withMinutesRequired(40);

        $this->assertEquals(1, $settings->getObjId());
        $this->assertEquals("0123456789", $settings->getPhone());
        $this->assertEquals("pin", $settings->getPin());
        $this->assertEquals(25, $settings->getMinutesRequired());
        $this->assertFalse($settings->isUploadRequired());

        $this->assertEquals(1, $new_settings->getObjId());
        $this->assertEquals("0123456789", $new_settings->getPhone());
        $this->assertEquals("pin", $new_settings->getPin());
        $this->assertEquals(40, $new_settings->getMinutesRequired());
        $this->assertFalse($new_settings->isUploadRequired());

        $this->assertNotSame($new_settings, $settings);

        return $settings;
    }

    /**
     * @depends test_withMinutesRequired
     */
    public function test_withIsUploadRequiredd($settings)
    {
        $new_settings = $settings->withIsUploadRequired(true);

        $this->assertEquals(1, $settings->getObjId());
        $this->assertEquals("0123456789", $settings->getPhone());
        $this->assertEquals("pin", $settings->getPin());
        $this->assertEquals(25, $settings->getMinutesRequired());
        $this->assertFalse($settings->isUploadRequired());

        $this->assertEquals(1, $new_settings->getObjId());
        $this->assertEquals("0123456789", $new_settings->getPhone());
        $this->assertEquals("pin", $new_settings->getPin());
        $this->assertEquals(25, $new_settings->getMinutesRequired());
        $this->assertTrue($new_settings->isUploadRequired());

        $this->assertNotSame($new_settings, $settings);
    }
}
