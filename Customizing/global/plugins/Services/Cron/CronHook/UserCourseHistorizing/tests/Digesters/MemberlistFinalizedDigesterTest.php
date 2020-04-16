<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use PHPUnit\Framework\TestCase;

class MemberlistFinalizedDigesterTest extends TestCase
{
    /**
     * @var MemberlistFinalizedDigester
     */
    protected $obj;

    public function setUp() : void
    {
        $this->obj = new MemberlistFinalizedDigester();
    }

    public function testDigestWithFinalizedDate() : void
    {
        $payload = [
            'finalized_date' => '2020-01-01'
        ];

        $result = $this->obj->digest($payload);

        $this->assertEquals('2020-01-01', $result['participation_finalized_date']);
    }

    public function testDigestWithoutPayload()
    {
        $date = date('Y-m-d');
        $result = $this->obj->digest([]);

        $this->assertEquals($date, $result['participation_finalized_date']);
    }
}
