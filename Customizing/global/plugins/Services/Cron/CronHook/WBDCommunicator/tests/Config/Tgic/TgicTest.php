<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WBDCommunicator\Config\Tgic;

use PHPUnit\Framework\TestCase;

class TgicTest extends TestCase
{
    public function test_create_instance()
    {
        $obj = new Tgic(
            "partner",
            "path",
            "123alle"
        );

        $this->assertInstanceOf(Tgic::class, $obj);
    }

    public function test_object_values()
    {
        $obj = new Tgic(
            "partner",
            "path",
            "123alle"
        );

        $this->assertInstanceOf(Tgic::class, $obj);

        $this->assertEquals("partner", $obj->getPartnerId());
        $this->assertEquals("path", $obj->getCertstore());
        $this->assertEquals("123alle", $obj->getPassword());
    }
}
