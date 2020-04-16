<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use PHPUnit\Framework\TestCase;

class IDDDigesterTest extends TestCase
{
    /**
     * @var IDDDigester
     */
    protected $obj;

    public function setUp() : void
    {
        $this->obj = new IDDDigester();
    }

    public function testDigestWithEmptyPayload() : void
    {
        $result = $this->obj->digest([]);
        $this->assertTrue(count($result) === 0);
    }

    public function testDigestWithMinutesPayload() : void
    {
        $payload = [
            'minutes' => 300
        ];

        $result = $this->obj->digest($payload);
        $this->assertEquals(300, $result['idd_learning_time']);
        $this->assertNull($result['custom_p_status']);
    }

    public function testDigestWithLPValuePayload() : void
    {
        $payload = [
            'lp_value' => 'test1'
        ];

        $result = $this->obj->digest($payload);
        $this->assertEquals('test1', $result['custom_p_status']);
        $this->assertNull($result['idd_learning_time']);
    }

    public function testDigestWithPayload() : void
    {
        $payload = [
            'minutes' => 333,
            'lp_value' => 'test2'
        ];

        $result = $this->obj->digest($payload);

        $this->assertEquals(333, $result['idd_learning_time']);
        $this->assertEquals('test2', $result['custom_p_status']);
    }
}
