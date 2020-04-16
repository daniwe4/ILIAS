<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WBDCommunicator\Config\WBD;

use PHPUnit\Framework\TestCase;

class SystemTest extends TestCase
{
    public function test_create_instance()
    {
        $obj = new System(
            System::WBD_TEST
        );

        $this->assertInstanceOf(System::class, $obj);
    }

    public function test_is_test()
    {
        $obj = new System(
            System::WBD_TEST
        );

        $this->assertInstanceOf(System::class, $obj);
        $this->assertTrue($obj->isTest());
        $this->assertFalse($obj->isLive());
    }

    public function test_is_live()
    {
        $obj = new System(
            System::WBD_LIVE
        );

        $this->assertInstanceOf(System::class, $obj);
        $this->assertTrue($obj->isLive());
        $this->assertFalse($obj->isTest());
    }

    public function test_create_instance_failed()
    {
        try {
            $obj = new System(
                "test"
            );
            $this->assertFalse(true);
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
    }
}
