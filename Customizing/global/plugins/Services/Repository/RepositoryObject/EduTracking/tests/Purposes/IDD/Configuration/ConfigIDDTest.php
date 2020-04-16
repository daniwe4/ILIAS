<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\EduTracking\Purposes\IDD\Configuration;

use PHPUnit\Framework\TestCase;

class ConfigIDDTest extends TestCase
{
    public function testCreate() : void
    {
        $obj = new ConfigIDD(22, true);

        $this->assertEquals(22, $obj->getId());
        $this->assertTrue($obj->getAvailable());
    }
}
