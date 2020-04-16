<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use PHPUnit\Framework\TestCase;

class UpdateSessionDigesterTestObject extends UpdateSessionDigester
{
    /**
     * @var Mocks
     */
    protected $mocks;

    /**
     * @var \ilObjSession
     */
    protected $session;

    public function __construct()
    {
        $this->mocks = new Mocks();
    }

    public function setSessionObject(\ilObjSession $session) : void
    {
        $this->session = $session;
    }

    protected function getSessionByPayload(array $payload)
    {
        return $this->session;
    }

    protected function getCrsIdByPayload(array $payload)
    {
        return $this->mocks->getCrsMock()->getId();
    }
}

class UpdateSessionDigesterTest extends TestCase
{
    /**
     * @var UpdateSessionDigesterTestObject
     */
    protected $obj;

    /**
     * @var Mocks
     */
    protected $mocks;

    public function setUp() : void
    {
        $this->obj = new UpdateSessionDigesterTestObject();
        $this->mocks = new Mocks();
    }

    public function testDigestWithDisabledFulltimeAndDaysOffsetNotNull() : void
    {
        $appointment = $this->mocks->getIlSessionAppointment();
        $appointment
            ->expects($this->once())
            ->method('enabledFullTime')
            ->willReturn(0)
        ;
        $appointment
            ->expects($this->once())
            ->method('getDaysOffset')
            ->willReturn(1)
        ;

        $session = $this->createMock(\ilObjSession::class);
        $session
            ->expects($this->once())
            ->method('getFirstAppointment')
            ->willReturn($appointment)
        ;
        $session
            ->expects($this->once())
            ->method('getId')
            ->willReturn(33)
        ;

        $this->obj->setSessionObject($session);

        $result = $this->obj->digest([]);

        $this->assertEquals(33, $result['session_id']);
        $this->assertEquals(22, $result['crs_id']);
        $this->assertEquals(false, $result['removed']);
        $this->assertEquals(false, $result['fullday']);
        $this->assertEquals('2020-01-01', $result['begin_date']);
        $this->assertEquals('2020-01-01', $result['end_date']);
        $this->assertEquals('12345', $result['start_time']);
        $this->assertEquals('54321', $result['end_time']);
    }


    public function testDigestWithEnabledFulltimeAndDaysOffsetNotNull() : void
    {
        $appointment = $this->mocks->getIlSessionAppointment();
        $appointment
            ->expects($this->once())
            ->method('enabledFullTime')
            ->willReturn(1)
        ;
        $appointment
            ->expects($this->once())
            ->method('getDaysOffset')
            ->willReturn(1)
        ;

        $session = $this->createMock(\ilObjSession::class);
        $session
            ->expects($this->once())
            ->method('getFirstAppointment')
            ->willReturn($appointment)
        ;
        $session
            ->expects($this->once())
            ->method('getId')
            ->willReturn(33)
        ;

        $this->obj->setSessionObject($session);

        $result = $this->obj->digest([]);

        $this->assertEquals(33, $result['session_id']);
        $this->assertEquals(22, $result['crs_id']);
        $this->assertEquals(false, $result['removed']);
        $this->assertEquals(true, $result['fullday']);
        $this->assertEquals('2020-01-01', $result['begin_date']);
        $this->assertEquals('2020-01-01', $result['end_date']);
        $this->assertEquals(0, $result['start_time']);
        $this->assertEquals(0, $result['end_time']);
    }

    public function testDigestWithEnabledFulltimeAndDaysOffsetNull() : void
    {
        $appointment = $this->mocks->getIlSessionAppointment();
        $appointment
            ->expects($this->once())
            ->method('enabledFullTime')
            ->willReturn(1)
        ;
        $appointment
            ->expects($this->once())
            ->method('getDaysOffset')
            ->willReturn(null)
        ;

        $session = $this->createMock(\ilObjSession::class);
        $session
            ->expects($this->once())
            ->method('getFirstAppointment')
            ->willReturn($appointment)
        ;
        $session
            ->expects($this->once())
            ->method('getId')
            ->willReturn(33)
        ;

        $this->obj->setSessionObject($session);

        $result = $this->obj->digest([]);

        $this->assertEquals(33, $result['session_id']);
        $this->assertEquals(22, $result['crs_id']);
        $this->assertEquals(false, $result['removed']);
        $this->assertEquals(true, $result['fullday']);
        $this->assertEquals('2020-01-01', $result['begin_date']);
        $this->assertEquals('2020-01-01', $result['end_date']);
        $this->assertEquals(0, $result['start_time']);
        $this->assertEquals(0, $result['end_time']);
    }

    public function testDigestWithDisabledFulltimeAndDaysOffsetNull() : void
    {
        $appointment = $this->mocks->getIlSessionAppointment();
        $appointment
            ->expects($this->once())
            ->method('enabledFullTime')
            ->willReturn(0)
        ;
        $appointment
            ->expects($this->once())
            ->method('getDaysOffset')
            ->willReturn(null)
        ;

        $session = $this->createMock(\ilObjSession::class);
        $session
            ->expects($this->once())
            ->method('getFirstAppointment')
            ->willReturn($appointment)
        ;
        $session
            ->expects($this->once())
            ->method('getId')
            ->willReturn(33)
        ;

        $this->obj->setSessionObject($session);

        $result = $this->obj->digest([]);

        $this->assertEquals(33, $result['session_id']);
        $this->assertEquals(22, $result['crs_id']);
        $this->assertEquals(false, $result['removed']);
        $this->assertEquals(false, $result['fullday']);
        $this->assertEquals('2020-01-01', $result['begin_date']);
        $this->assertEquals('2020-01-01', $result['end_date']);
        $this->assertEquals('2020', $result['start_time']);
        $this->assertEquals('2020', $result['end_time']);
    }
}
