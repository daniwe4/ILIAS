<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WBDCommunicator\Config\UDF;

use PHPUnit\Framework\TestCase;

class UDFDefinitionTest extends TestCase
{
    public function test_create_instance()
    {
        $obj = new UDFDefinition(
            "wbd_id",
            1
        );

        $this->assertInstanceOf(UDFDefinition::class, $obj);
    }

    public function test_object_values()
    {
        $obj = new UDFDefinition(
            "wbd_id",
            1
        );

        $this->assertInstanceOf(UDFDefinition::class, $obj);

        $this->assertEquals("wbd_id", $obj->getField());
        $this->assertEquals(1, $obj->getFieldId());
    }
}
