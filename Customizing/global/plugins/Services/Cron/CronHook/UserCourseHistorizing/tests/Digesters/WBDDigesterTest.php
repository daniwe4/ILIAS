<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use PHPUnit\Framework\TestCase;

class WBDDigesterTest extends TestCase
{
    /**
     * @var WBDDigester
     */
    protected $obj;

    public function setUp() : void
    {
        $this->obj = new WBDDigester();
    }

    public function testDigestWithoutPayload() : void
    {
        $result = $this->obj->digest([]);

        $this->assertEquals(0, count($result));
    }

    public function testDigestWithPayload() : void
    {
        $payload = [
            'nonsense' => 'nonsense'
        ];

        $result = $this->obj->digest($payload);

        $this->assertEquals(0, count($result));
    }
}
