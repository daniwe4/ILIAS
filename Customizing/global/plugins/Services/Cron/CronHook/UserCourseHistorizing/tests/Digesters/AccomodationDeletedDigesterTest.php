<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use PHPUnit\Framework\TestCase;

class AccomodationDeletedDigesterTest extends TestCase
{
    public function testDigest() : void
    {
        $obj = new AccomodationDeletedDigester();

        $payload = ['xoac_venue' => 'venue'];
        $result = $obj->digest($payload);
        $this->assertEquals('venue', $result['accomodation']);

        $payload = ['xoac_venue_from_course' => 'venue_from_course'];
        $result = $obj->digest($payload);
        $this->assertEquals('venue_from_course', $result['venue_from_course']);
    }
}
