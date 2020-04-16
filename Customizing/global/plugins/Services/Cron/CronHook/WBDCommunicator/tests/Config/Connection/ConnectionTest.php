<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WBDCommunicator\Config\Connection;

use PHPUnit\Framework\TestCase;

class ConnectionTest extends TestCase
{
    public function test_create_instance()
    {
        $obj = new Connection(
            "host",
            "port",
            "endpoint",
            "namespace",
            "name"
        );

        $this->assertInstanceOf(Connection::class, $obj);
    }

    public function test_object_values()
    {
        $obj = new Connection(
            "host",
            "port",
            "endpoint",
            "namespace",
            "name"
        );

        $this->assertInstanceOf(Connection::class, $obj);

        $this->assertEquals("host", $obj->getHost());
        $this->assertEquals("port", $obj->getPort());
        $this->assertEquals("endpoint", $obj->getEndpoint());
        $this->assertEquals("namespace", $obj->getNamespace());
        $this->assertEquals("name", $obj->getName());
    }
}
