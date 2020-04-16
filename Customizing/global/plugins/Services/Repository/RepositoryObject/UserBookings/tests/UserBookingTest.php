<?php
use PHPUnit\Framework\TestCase;
use CaT\Plugins\UserBookings\UserBooking\UserBooking;

/**
 * @group needsInstalledILIAS
 */
class UserBookingTest extends TestCase
{
    public function test_object()
    {
        require_once("Services/Calendar/classes/class.ilDateTime.php");
        $start = new ilDateTime(time(), IL_CAL_UNIX);
        $end = new ilDateTime(time(), IL_CAL_UNIX);
        $user_booking = new UserBooking(
            "Titel",
            "Training",
            $start,
            array("Ziel", "Gruppe"),
            "Ziele",
            array("Themen", "Thema"),
            $end,
            "Im Büro",
            "Vorgebirgstr Köln",
            "20 €"
        );

        $this->assertEquals("Titel", $user_booking->getTitle());
        $this->assertEquals("Training", $user_booking->getType());
        $this->assertEquals($start, $user_booking->getBeginDate());
        $this->assertSame($start, $user_booking->getBeginDate());
        $this->assertEquals(array("Ziel", "Gruppe"), $user_booking->getTargetGroup());
        $this->assertEquals("Ziele", $user_booking->getGoals());
        $this->assertEquals(array("Themen", "Thema"), $user_booking->getTopics());
        $this->assertEquals($end, $user_booking->getEndDate());
        $this->assertSame($end, $user_booking->getEndDate());
        $this->assertEquals("Im Büro", $user_booking->getLocation());
        $this->assertEquals("Vorgebirgstr Köln", $user_booking->getAddress());
        $this->assertEquals("20 €", $user_booking->getFee());
    }
}
