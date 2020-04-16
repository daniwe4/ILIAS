<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use PHPUnit\Framework\TestCase;

class CourseDatesDigesterTest extends TestCase
{
    /**
     * @var Mocks
     */
    protected $mock;

    /**
     * @var CourseDatesDigester
     */
    protected $obj;

    public function setUp() : void
    {
        $this->mock = new Mocks();
        $this->obj = new CourseDatesDigester();
    }

    public function testDigestWithoutSpecificDate() : void
    {
        $crs = $this->mock->getCrsMock();
        $crs
            ->expects($this->once())
            ->method('getCourseStart')
            ->willReturn(null)
        ;
        $crs
            ->expects($this->once())
            ->method('getCourseEnd')
            ->willReturn(null)
        ;

        $payload = [
            'object' => $crs
        ];

        $result = $this->obj->digest($payload);

        $this->assertEquals('0001-01-01', $result['begin_date']);
        $this->assertEquals('0001-01-01', $result['end_date']);
    }

    public function testDigestWithSpecificDate() : void
    {
        $crs = $this->mock->getCrsMock();
        $date = $this->mock->getIlDateMock();
        $crs
            ->expects($this->once())
            ->method('getCourseStart')
            ->willReturn($date)
        ;
        $crs
            ->expects($this->once())
            ->method('getCourseEnd')
            ->willReturn($date)
        ;

        $payload = [
            'object' => $crs
        ];

        $result = $this->obj->digest($payload);

        $this->assertEquals('2020-01-01', $result['begin_date']);
        $this->assertEquals('2020-01-01', $result['end_date']);
    }
}
