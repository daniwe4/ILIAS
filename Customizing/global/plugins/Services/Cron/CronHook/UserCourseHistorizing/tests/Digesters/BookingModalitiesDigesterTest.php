<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use PHPUnit\Framework\TestCase;
use CaT\Plugins\BookingModalities\Settings\Booking;
use CaT\Plugins\BookingModalities\Settings\Storno;
use CaT\Plugins\BookingModalities\Settings\Member;

require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/classes/class.ilObjBookingModalities.php";

class BookingModalitiesDigesterTest extends TestCase
{
    /**
     * @var Mocks
     */
    protected $mocks;

    /**
     * @var \ilObjCourse|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $crs;

    /**
     * @var \ilTree|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $tree;

    /**
     * @var \ilObjectDefinition|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $obj_def;

    /**
     * @var \ilObjBookingModalities|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $object;

    public function setUp() : void
    {
        $this->mocks = new Mocks();

        $this->crs = $this->mocks->getCrsMock();

        $this->tree = $this->createMock(\ilTree::class);
        $this->obj_def = $this->createMock(\ilObjectDefinition::class);

        $booking = $this->createMock(Booking\Booking::class);
        $booking
            ->expects($this->any())
            ->method('getDeadline')
            ->willReturn(10);
        $booking
            ->expects($this->any())
            ->method('getToBeAcknowledged')
            ->willReturn(true)
        ;

        $storno = $this->createMock(Storno\Storno::class);
        $storno
            ->expects($this->any())
            ->method('getDeadline')
            ->willReturn(20)
        ;

        $members = $this->createMock(Member\Member::class);
        $members
            ->expects($this->any())
            ->method('getMax')
            ->willReturn(30)
        ;
        $members
            ->expects($this->any())
            ->method('getMin')
            ->willReturn(40)
        ;

        $this->object = $this->createMock('ilObjBookingModalities');
        $this->object
            ->expects($this->any())
            ->method('getBooking')
            ->willReturn($booking)
        ;
        $this->object
            ->expects($this->any())
            ->method('getStorno')
            ->willReturn($storno)
        ;
        $this->object
            ->expects($this->any())
            ->method('getmember')
            ->willReturn($members)
        ;
    }

    public function testDigestCreateWithoutCourseStartDate() : void
    {
        $payload = [
            'object' => $this->object,
            'parent_course' => $this->crs
        ];

        $obj = new BookingModalitiesDigester('create', $this->tree, $this->obj_def);
        $result = $obj->digest($payload);

        $this->assertEquals('0001-01-01', $result['booking_dl_date']);
        $this->assertEquals('0001-01-01', $result['storno_dl_date']);
        $this->assertEquals(10, $result['booking_dl']);
        $this->assertEquals(20, $result['storno_dl']);
        $this->assertEquals(30, $result['max_members']);
        $this->assertEquals(40, $result['min_members']);
        $this->assertEquals(22, $result['crs_id']);
        $this->assertTrue($result['to_be_acknowledged']);
    }

    public function testDigestCreateWithCourseStartDate()
    {
        $this->crs
            ->expects($this->any())
            ->method('getCourseStart')
            ->willReturn($this->mocks->getIlDateMock())
        ;

        $payload = [
            'object' => $this->object,
            'parent_course' => $this->crs
        ];

        $obj = new BookingModalitiesDigester('create', $this->tree, $this->obj_def);
        $result = $obj->digest($payload);

        $this->assertEquals('2019-12-22', $result['booking_dl_date']);
        $this->assertEquals('2019-12-12', $result['storno_dl_date']);
        $this->assertEquals(10, $result['booking_dl']);
        $this->assertEquals(20, $result['storno_dl']);
        $this->assertEquals(30, $result['max_members']);
        $this->assertEquals(40, $result['min_members']);
        $this->assertEquals(22, $result['crs_id']);
        $this->assertTrue($result['to_be_acknowledged']);
    }

    public function testDigestDelete() : void
    {
        $payload = [
            'object' => $this->object,
            'parent_course' => $this->crs
        ];

        $obj = new BookingModalitiesDigester('delete', $this->tree, $this->obj_def);
        $result = $obj->digest($payload);

        $this->assertEquals('0001-01-01', $result['booking_dl_date']);
        $this->assertEquals('0001-01-01', $result['storno_dl_date']);
        $this->assertEquals(0, $result['booking_dl']);
        $this->assertEquals(0, $result['storno_dl']);
        $this->assertEquals(0, $result['max_members']);
        $this->assertEquals(0, $result['min_members']);
        $this->assertEquals(22, $result['crs_id']);
        $this->assertFalse($result['to_be_acknowledged']);
    }
}
