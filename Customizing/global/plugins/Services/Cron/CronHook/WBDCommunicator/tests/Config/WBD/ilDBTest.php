<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WBDCommunicator\Config\WBD;

use PHPUnit\Framework\TestCase;

class ilDBTest extends TestCase
{
    public function test_create_instance()
    {
        $setting = $this->getMockBuilder(\ilSetting::class)
            ->getMock()
        ;
        $db = new ilDB($setting);

        $this->assertInstanceOf(ilDB::class, $db);
    }
}
