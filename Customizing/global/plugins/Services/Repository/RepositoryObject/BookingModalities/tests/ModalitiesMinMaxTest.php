<?php
use CaT\Plugins\BookingModalities\Settings\Booking\Booking;
use CaT\Plugins\BookingModalities\Settings\Waitinglist\Waitinglist;
use CaT\Plugins\BookingModalities\Settings\Storno\Storno;
use CaT\Plugins\BookingModalities\Settings\Member\Member;
use CaT\Plugins\BookingModalities\ObjBookingModalities;
use PHPUnit\Framework\TestCase;

/**
 * A BookingModalities-Object as input for the min/max functions
 */
class MockModalities implements ObjBookingModalities
{
    protected $booking;
    protected $waiting;
    protected $storno;
    protected $member;
    public function __construct(Booking $booking, Waitinglist $waiting, Storno $storno, Member $member)
    {
        $this->booking = $booking;
        $this->waiting = $waiting;
        $this->storno = $storno;
        $this->member = $member;
    }

    public function getBooking()
    {
        return $this->booking;
    }
    public function getStorno()
    {
        return $this->storno;
    }
    public function getMember()
    {
        return $this->member;
    }
    public function getWaitinglist()
    {
        return $this->waiting;
    }
}


/**
 * Test best values / worst values for Modalities-Plugin
 * @group needsInstalledILIAS
 */
class ModalitiesMinMaxTest extends TestCase
{
    public function setUp() : void
    {
        require_once(__DIR__ . '/../classes/class.ilBookingModalitiesPlugin.php');
        $this->bkm1 = new MockModalities(
            //$obj_id, $beginning = null, $deadline = null, $modus = null, array $approve_roles = []
            new Booking(1, 20, 10, "self"),
            //$obj_id, $cancellation = null, $max = null, $modus = null
            new Waitinglist(1, 20, 10, "with_auto_move_up"),
            //$obj_id, $deadline = null, $hard_deadline = null, $modus = null, $reason_type = null, array $approve_roles = []
            new Storno(1, 20, 10, "self"),
            //$obj_id, $min = null, $max = null
            new Member(1, 10, 20)
        );
        $this->bkm2 = new MockModalities(
            new Booking(1, 2, 12, "self"),
            new Waitinglist(1, 12, 22, "no_waitinglist"),
            new Storno(1, 12, 22, "self"),
            new Member(1, 2, 12)
        );

        $this->bkm2a = clone $this->bkm2;

        $this->bkm3 = new MockModalities(
            new Booking(1, 3, 13, "self"),
            new Waitinglist(1, 23, 3, "without_auto_move_up"),
            new Storno(1, 30, 30, "self"),
            new Member(1, 3, 33)
        );

        //null-values...
        $this->bkm0 = new MockModalities(
            new Booking(1),
            new Waitinglist(1),
            new Storno(1),
            new Member(1)
        );
    }


    public function testBestForUser()
    {
        /* Best for User:
        *
        * min_member: lowest
        * max_member: highest
        * booking_start: earliest (highest)
        * booking_end: latest (lowest)
        * waiting_list_mode: with moveup
        */

        $bkms = array($this->bkm1, $this->bkm2, $this->bkm2a, $this->bkm3);

        list($min_member, $max_member,
            $booking_start, $booking_end,
            $waiting_list_mode
            ) = ilBookingModalitiesPlugin::bestValuesForUser($bkms);

        $this->assertEquals(2, $min_member);
        $this->assertEquals(33, $max_member);
        $this->assertEquals(20, $booking_start);
        $this->assertEquals(10, $booking_end);
        $this->assertEquals('with_auto_move_up', $waiting_list_mode);
    }

    public function testBestForUserWithNullValues()
    {
        $bkms = array($this->bkm1, $this->bkm2, $this->bkm2a,
            $this->bkm3, $this->bkm0);

        list($min_member, $max_member,
            $booking_start, $booking_end,
            $waiting_list_mode
            ) = ilBookingModalitiesPlugin::bestValuesForUser($bkms);

        $this->assertEquals(null, $min_member);
        $this->assertEquals(null, $max_member);
        $this->assertEquals(null, $booking_start);
        $this->assertEquals(null, $booking_end);
        $this->assertEquals('with_auto_move_up', $waiting_list_mode);
    }


    public function testMostRestrictive()
    {
        /* Most restrictive:
        *
        * min_member: highest
        * max_member: lowest
        * booking_start: latest (lowest)
        * booking_end: earliest (highest)
        * waiting_list_mode: no waiting
        */
        $bkms = array($this->bkm1, $this->bkm2, $this->bkm2a,
            $this->bkm3, $this->bkm0);

        list($min_member, $max_member,
            $booking_start, $booking_end,
            $waiting_list_mode
            ) = ilBookingModalitiesPlugin::mostRestrictiveValues($bkms);

        $this->assertEquals(10, $min_member);
        $this->assertEquals(12, $max_member);
        $this->assertEquals(2, $booking_start);
        $this->assertEquals(13, $booking_end);
        $this->assertEquals('no_waitinglist', $waiting_list_mode);
    }
}
