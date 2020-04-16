<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\EduTracking\Purposes\WBD\Configuration;

use PHPUnit\Framework\TestCase;

class ConfigWBDTest extends TestCase
{
    public function testCreateWithoutUserId() : void
    {
        $obj = new ConfigWBD(22, true, 'test_contact');

        $this->assertEquals(22, $obj->getId());
        $this->assertTrue($obj->getAvailable());
        $this->assertEquals('test_contact', $obj->getContact());
        $this->assertNull($obj->getUserId());
    }

    public function testCreateWithUserId() : void
    {
        $obj = new ConfigWBD(22, true, 'test_contact', 6);

        $this->assertEquals(22, $obj->getId());
        $this->assertTrue($obj->getAvailable());
        $this->assertEquals('test_contact', $obj->getContact());
        $this->assertEquals(6, $obj->getUserId());
    }
}
