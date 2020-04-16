<?php
use CaT\Plugins\Accomodation\Reservation\Reservation;
use PHPUnit\Framework\TestCase;

/**
 * Testing the Reservation
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @group needsInstalledILIAS
 */
class ReservationTest extends TestCase
{
    protected $obj_id = 1;
    protected $oac_obj_id = 2;
    protected $ses_obj_id = 3;
    protected $usr_id = 4;
    protected $selfpay = false;
    protected $date;

    public function setUp() : void
    {
        require_once('Services/Calendar/classes/class.ilDate.php');
        $this->date = new ilDate('2017-12-24', IL_CAL_DATE);
    }

    public function test_construction()
    {
        $r = new Reservation(
            $this->obj_id,
            $this->oac_obj_id,
            $this->usr_id,
            $this->date,
            $this->ses_obj_id,
            $this->selfpay
        );
        $this->assertInstanceOf(Reservation::class, $r);
        return $r;
    }

    /**
     * @depends test_construction
     */
    public function test_getters($r)
    {
        $this->assertEquals($this->obj_id, $r->getId());
        $this->assertEquals($this->oac_obj_id, $r->getAccomodationObjId());
        $this->assertEquals($this->ses_obj_id, $r->getSessionObjId());
        $this->assertEquals($this->usr_id, $r->getUserId());
        $this->assertEquals($this->date, $r->getDate());
        $this->assertEquals($this->selfpay, $r->getSelfpay());
    }
}
