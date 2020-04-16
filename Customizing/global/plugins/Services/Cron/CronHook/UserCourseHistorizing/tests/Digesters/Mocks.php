<?php


/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use PHPUnit\Framework\TestCase;

class Mocks extends TestCase
{
    public function getCrsMock()
    {
        $crs = $this->createMock(\ilObjCourse::class);
        $crs
            ->expects($this->any())
            ->method('getId')
            ->willReturn(22)
        ;
        $crs
            ->expects($this->any())
            ->method('getSubItems')
            ->willReturn([
                'xbkm' => [],
                'xcps' => [
                    ['child' => 10]
                ]
            ])
        ;

        return $crs;
    }

    public function getIlDateMock()
    {
        $date = $this->createMock(\ilDate::class);
        $date
            ->expects($this->any())
            ->method('get')
            ->willReturn('2020-01-01')
        ;
        return $date;
    }

    public function getIlSessionAppointment()
    {
        $appointment = $this->createMock(\ilSessionAppointment::class);
        $appointment
            ->expects($this->any())
            ->method('getStartingTime')
            ->willReturn('12345')
        ;
        $appointment
            ->expects($this->any())
            ->method('getEndingTime')
            ->willReturn('54321')
        ;
        $appointment
            ->expects($this->any())
            ->method('getStart')
            ->willReturn($this->getIlDateMock())
        ;
        $appointment
            ->expects($this->any())
            ->method('getEnd')
            ->willReturn($this->getIlDateMock())
        ;

        return $appointment;
    }
}
