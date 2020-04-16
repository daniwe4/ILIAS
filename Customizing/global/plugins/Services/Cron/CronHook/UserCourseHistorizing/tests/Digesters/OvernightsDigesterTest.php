<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use PHPUnit\Framework\TestCase;
use CaT\Plugins\Accomodation\Reservation\Reservation;

class OvernightsDigesterTest extends TestCase
{
    public function testDigest() : void
    {
        $mocks = new Mocks();

        $reservation = $this->createMock(Reservation::class);
        $reservation
            ->expects($this->once())
            ->method('getDate')
            ->willReturn($mocks->getIlDateMock())
        ;

        $payload = [
            'xoac_reservations' => [
                $reservation
            ],
            'xoac_priorday' => true,
            'xoac_followingday' => true
        ];

        $obj = new OvernightsDigester();
        $result = $obj->digest($payload);

        $this->assertEquals('2020-01-01', $result['nights'][0]);
        $this->assertTrue($result['prior_night']);
        $this->assertTrue($result['following_night']);
    }
}
