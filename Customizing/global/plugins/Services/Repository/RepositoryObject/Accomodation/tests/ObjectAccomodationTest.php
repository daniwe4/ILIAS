<?php

use CaT\Plugins\Accomodation as A;
use PHPUnit\Framework\TestCase;

/**
 * Testing the AccomodationObject
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @group needsInstalledILIAS
 */
class AccomodationTest extends TestCase
{

    /**
     * @var ObjAccomodation
     */
    protected $obj;

    public function setUp() : void
    {
        include_once('./Services/PHPUnit/classes/class.ilUnitUtil.php');
        \ilUnitUtil::performInitialisation();

        require_once(__DIR__ . '/../classes/class.ilObjAccomodation.php');
        $this->obj = new \ilObjAccomodation();
    }

    public function test_construction()
    {
        $this->assertInstanceOf(A\ObjAccomodation::class, $this->obj);

        $this->obj->initType();
        $this->assertEquals('xoac', $this->obj->getType());
    }

    public function test_db_construction()
    {
        $this->assertInstanceOf(A\ilActions::class, $this->obj->getActions());
        $this->assertInstanceOf(A\ObjSettings\DB::class, $this->obj->getObjSettingsDB());
        $this->assertInstanceOf(A\Reservation\DB::class, $this->obj->getReservationDB());
        $this->assertInstanceOf(A\Venue\DB::class, $this->obj->getVenueDB());
        $this->assertInstanceOf(A\Session\DB::class, $this->obj->getSessionDB());
    }
}
