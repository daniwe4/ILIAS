<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WBDManagement\GutBeraten;

use PHPUnit\Framework\TestCase;

class WBDDataTest extends TestCase
{
    public function test_create_instance()
    {
        $obj = new WBDData(
            10,
            "20190101-123456-12",
            "Bildungsdienstleister",
            new \DateTime()
        );

        $this->assertInstanceOf(WBDData::class, $obj);
    }

    public function test_object_values()
    {
        $date = new \DateTime();
        $obj = new WBDData(
            10,
            "20190101-123456-12",
            "Bildungsdienstleister",
            $date
        );

        $this->assertInstanceOf(WBDData::class, $obj);
        $this->assertEquals(10, $obj->getUsrId());
        $this->assertEquals("20190101-123456-12", $obj->getWbdId());
        $this->assertEquals("Bildungsdienstleister", $obj->getStatus());
        $this->assertSame($date, $obj->getApproveDate());
        $this->assertEquals(
            $date->format("Y-m-d H:i:s"),
            $obj->getApproveDate()->format("Y-m-d H:i:s")
        );
    }
}
