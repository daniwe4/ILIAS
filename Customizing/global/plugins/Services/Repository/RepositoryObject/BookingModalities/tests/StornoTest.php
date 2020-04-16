<?php

use CaT\Plugins\BookingModalities\Settings\Storno\Storno;
use PHPUnit\Framework\TestCase;

/**
 * Sample for PHP Unit tests
 */
class StornoTest extends TestCase
{
    public function test_create()
    {
        $storno = new Storno(1, 20, 10, "self");

        $this->assertEquals(1, $storno->getObjId());
        $this->assertEquals(20, $storno->getDeadline());
        $this->assertEquals(10, $storno->getHardDeadline());
        $this->assertEquals("self", $storno->getModus());

        return $storno;
    }

    /**
     * @depends test_create
     */
    public function test_withEnd($storno)
    {
        $new_storno = $storno->withDeadline(15);

        $this->assertEquals(1, $storno->getObjId());
        $this->assertEquals(20, $storno->getDeadline());
        $this->assertEquals(10, $storno->getHardDeadline());
        $this->assertEquals("self", $storno->getModus());

        $this->assertEquals(1, $new_storno->getObjId());
        $this->assertEquals(15, $new_storno->getDeadline());
        $this->assertEquals(10, $new_storno->getHardDeadline());
        $this->assertEquals("self", $new_storno->getModus());

        return $storno;
    }

    /**
     * @depends test_withEnd
     */
    public function test_withHardEnd($storno)
    {
        $new_storno = $storno->withHardDeadline(5);

        $this->assertEquals(1, $storno->getObjId());
        $this->assertEquals(20, $storno->getDeadline());
        $this->assertEquals(10, $storno->getHardDeadline());
        $this->assertEquals("self", $storno->getModus());

        $this->assertEquals(1, $new_storno->getObjId());
        $this->assertEquals(20, $new_storno->getDeadline());
        $this->assertEquals(5, $new_storno->getHardDeadline());
        $this->assertEquals("self", $new_storno->getModus());

        return $storno;
    }

    /**
     * @depends test_withHardEnd
     */
    public function test_withMode($storno)
    {
        $new_storno = $storno->withModus("superior");

        $this->assertEquals(1, $storno->getObjId());
        $this->assertEquals(20, $storno->getDeadline());
        $this->assertEquals(10, $storno->getHardDeadline());
        $this->assertEquals("self", $storno->getModus());

        $this->assertEquals(1, $new_storno->getObjId());
        $this->assertEquals(20, $new_storno->getDeadline());
        $this->assertEquals(10, $new_storno->getHardDeadline());
        $this->assertEquals("superior", $new_storno->getModus());
    }
}
