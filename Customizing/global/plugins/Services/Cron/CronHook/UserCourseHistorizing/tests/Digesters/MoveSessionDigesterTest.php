<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use PHPUnit\Framework\TestCase;

class MoveSessionDigesterTestObject extends MoveSessionDigester
{
    protected function getSesionIdByPalyoad(array $payload)
    {
        return 33;
    }

    protected function getCrsIdByPayload(array $payload)
    {
        return 22;
    }
}

class MoveSessionDigesterTest extends TestCase
{
    /**
     * @var MoveSessionDigesterTestObject
     */
    protected $obj;

    public function setUp() : void
    {
        $this->obj = new MoveSessionDigesterTestObject();
    }

    public function testDigest() : void
    {
        $result = $this->obj->digest([]);


        $this->assertEquals(33, $result['session_id']);
        $this->assertEquals(22, $result['crs_id']);
        $this->assertTrue($result['removed']);
        $this->assertFalse($result['fullday']);
        $this->assertEquals('0001-01-01', $result['begin_date']);
        $this->assertEquals('0001-01-01', $result['end_date']);
        $this->assertEquals(0, $result['start_time']);
        $this->assertEquals(0, $result['end_time']);
    }
}
