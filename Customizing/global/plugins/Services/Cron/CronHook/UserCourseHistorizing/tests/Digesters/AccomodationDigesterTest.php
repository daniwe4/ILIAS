<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use PHPUnit\Framework\TestCase;

class AccomodationDigesterTest extends TestCase
{
    public function testDigest() : void
    {
        $obj = new AccomodationDigester();

        $payload = ['xoac_venue' => 'venue'];
        $result = $obj->digest($payload);
        $this->assertEquals('venue', $result['accomodation']);

        $start = new \DateTime('now');
        $payload = ['xoac_date_start' => $start];
        $result = $obj->digest($payload);
        $this->assertEquals($start->format('Y-m-d'), $result['accomodation_date_start']);

        $end = new \DateTime('now');
        $payload = ['xoac_date_end' => $end];
        $result = $obj->digest($payload);
        $this->assertEquals($end->format('Y-m-d'), $result['accomodation_date_end']);

        $payload = ['xoac_venue_from_course' => 22];
        $result = $obj->digest($payload);
        $this->assertEquals(22, $result['venue_from_course']);
    }
}
