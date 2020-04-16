<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use PHPUnit\Framework\TestCase;

class GTIDigesterTest extends TestCase
{
    /**
     * @var GTIDigester
     */
    protected $obj;

    public function setUp() : void
    {
        $this->obj = new GTIDigester();
    }

    public function testDigestWithEmptyPayload() : void
    {
        $result = $this->obj->digest([]);
        $this->assertTrue(count($result) === 0);
    }

    public function testDigestWithPayload() : void
    {
        $payload = [
            'minutes' => 300
        ];

        $result = $this->obj->digest($payload);
        $this->assertEquals(300, $result['gti_learning_time']);
    }
}
