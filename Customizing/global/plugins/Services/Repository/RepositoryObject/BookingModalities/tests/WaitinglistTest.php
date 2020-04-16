<?php

use CaT\Plugins\BookingModalities\Settings\Waitinglist\Waitinglist;
use PHPUnit\Framework\TestCase;

/**
 * Sample for PHP Unit tests
 */
class WaitinglistTest extends TestCase
{
    public function test_create()
    {
        $waitinglist = new Waitinglist(1, 20, 10, "none");

        $this->assertEquals(1, $waitinglist->getObjId());
        $this->assertEquals(20, $waitinglist->getCancellation());
        $this->assertEquals(10, $waitinglist->getMax());
        $this->assertEquals("none", $waitinglist->getModus());

        return $waitinglist;
    }

    /**
     * @depends test_create
     */
    public function test_withCancellation($waitinglist)
    {
        $new_waitinglist = $waitinglist->withCancellation(15);

        $this->assertEquals(1, $waitinglist->getObjId());
        $this->assertEquals(20, $waitinglist->getCancellation());
        $this->assertEquals(10, $waitinglist->getMax());
        $this->assertEquals("none", $waitinglist->getModus());

        $this->assertEquals(1, $new_waitinglist->getObjId());
        $this->assertEquals(15, $new_waitinglist->getCancellation());
        $this->assertEquals(10, $new_waitinglist->getMax());
        $this->assertEquals("none", $new_waitinglist->getModus());

        return $waitinglist;
    }

    /**
     * @depends test_withCancellation
     */
    public function test_withMax($waitinglist)
    {
        $new_waitinglist = $waitinglist->withMax(5);

        $this->assertEquals(1, $waitinglist->getObjId());
        $this->assertEquals(20, $waitinglist->getCancellation());
        $this->assertEquals(10, $waitinglist->getMax());
        $this->assertEquals("none", $waitinglist->getModus());

        $this->assertEquals(1, $new_waitinglist->getObjId());
        $this->assertEquals(20, $new_waitinglist->getCancellation());
        $this->assertEquals(5, $new_waitinglist->getMax());
        $this->assertEquals("none", $new_waitinglist->getModus());

        return $waitinglist;
    }

    /**
     * @depends test_withMax
     */
    public function test_withMode($waitinglist)
    {
        $new_waitinglist = $waitinglist->withModus("without_automatic");

        $this->assertEquals(1, $waitinglist->getObjId());
        $this->assertEquals(20, $waitinglist->getCancellation());
        $this->assertEquals(10, $waitinglist->getMax());
        $this->assertEquals("none", $waitinglist->getModus());

        $this->assertEquals(1, $new_waitinglist->getObjId());
        $this->assertEquals(20, $new_waitinglist->getCancellation());
        $this->assertEquals(10, $new_waitinglist->getMax());
        $this->assertEquals("without_automatic", $new_waitinglist->getModus());
    }
}
