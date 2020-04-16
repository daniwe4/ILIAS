<?php

use CaT\Plugins\BookingModalities\Settings\Booking\Booking;
use PHPUnit\Framework\TestCase;

/**
 * Sample for PHP Unit tests
 */
class BookingTest extends TestCase
{
    public function test_create()
    {
        $booking = new Booking(1, 20, 10, "self");

        $this->assertEquals(1, $booking->getObjId());
        $this->assertEquals(20, $booking->getBeginning());
        $this->assertEquals(10, $booking->getDeadline());
        $this->assertEquals("self", $booking->getModus());
        $this->assertEquals(false, $booking->getToBeAcknowledged());
        return $booking;
    }

    /**
     * @depends test_create
     */
    public function test_withBegin($booking)
    {
        $new_booking = $booking->withBeginning(15);

        $this->assertEquals(1, $booking->getObjId());
        $this->assertEquals(20, $booking->getBeginning());
        $this->assertEquals(10, $booking->getDeadline());
        $this->assertEquals("self", $booking->getModus());
        $this->assertEquals(false, $booking->getToBeAcknowledged());

        $this->assertEquals(1, $new_booking->getObjId());
        $this->assertEquals(15, $new_booking->getBeginning());
        $this->assertEquals(10, $new_booking->getDeadline());
        $this->assertEquals("self", $new_booking->getModus());
        $this->assertEquals(false, $new_booking->getToBeAcknowledged());
        return $booking;
    }

    /**
     * @depends test_withBegin
     */
    public function test_withEnd($booking)
    {
        $new_booking = $booking->withDeadline(5);

        $this->assertEquals(1, $booking->getObjId());
        $this->assertEquals(20, $booking->getBeginning());
        $this->assertEquals(10, $booking->getDeadline());
        $this->assertEquals("self", $booking->getModus());
        $this->assertEquals(false, $booking->getToBeAcknowledged());

        $this->assertEquals(1, $new_booking->getObjId());
        $this->assertEquals(20, $new_booking->getBeginning());
        $this->assertEquals(5, $new_booking->getDeadline());
        $this->assertEquals("self", $new_booking->getModus());
        $this->assertEquals(false, $new_booking->getToBeAcknowledged());
        return $booking;
    }

    /**
     * @depends test_withEnd
     */
    public function test_withMode($booking)
    {
        $new_booking = $booking->withModus("trainer");

        $this->assertEquals(1, $booking->getObjId());
        $this->assertEquals(20, $booking->getBeginning());
        $this->assertEquals(10, $booking->getDeadline());
        $this->assertEquals("self", $booking->getModus());
        $this->assertEquals(false, $booking->getToBeAcknowledged());

        $this->assertEquals(1, $new_booking->getObjId());
        $this->assertEquals(20, $new_booking->getBeginning());
        $this->assertEquals(10, $new_booking->getDeadline());
        $this->assertEquals("trainer", $new_booking->getModus());
        $this->assertEquals(false, $new_booking->getToBeAcknowledged());
        return $booking;
    }

    /**
     * @depends test_withMode
     */
    public function test_withToBeAcknowledged($booking)
    {
        $new_booking = $booking->withToBeAcknowledged(true);

        $this->assertEquals(1, $booking->getObjId());
        $this->assertEquals(20, $booking->getBeginning());
        $this->assertEquals(10, $booking->getDeadline());
        $this->assertEquals("self", $booking->getModus());
        $this->assertEquals(false, $booking->getToBeAcknowledged());

        $this->assertEquals(1, $new_booking->getObjId());
        $this->assertEquals(20, $new_booking->getBeginning());
        $this->assertEquals(10, $new_booking->getDeadline());
        $this->assertEquals("self", $new_booking->getModus());
        $this->assertEquals(true, $new_booking->getToBeAcknowledged());
    }

    public function test_no_nullary_approve_roles()
    {
        $booking = new Booking(1, 20, 10, "self");
        $this->assertEquals([], $booking->getApproveRoles());

        $booking = $booking->withApproveRoles(["FK"]);
        $this->assertEquals(["FK"], $booking->getApproveRoles());

        $booking = $booking->withApproveRoles([]);
        $this->assertEquals([], $booking->getApproveRoles());
    }

    public function test_with_skip_duplicate_check()
    {
        $booking = new Booking(1, 20, 10, "self");
        $this->assertFalse($booking->getSkipDuplicateCheck());

        $booking = $booking->withSkipDuplicateCheck(true);
        $this->assertTrue($booking->getSkipDuplicateCheck());

        $booking = $booking->withSkipDuplicateCheck(false);
        $this->assertFalse($booking->getSkipDuplicateCheck());
    }
}
