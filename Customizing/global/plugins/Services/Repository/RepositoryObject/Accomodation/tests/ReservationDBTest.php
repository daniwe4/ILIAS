<?php
use CaT\Plugins\Accomodation\Reservation\Reservation;
use CaT\Plugins\Accomodation\Reservation\DB;
use CaT\Plugins\Accomodation\Reservation\ilDB;
use PHPUnit\Framework\TestCase;

class RMockDB extends ilDB
{
    const TABLE_NAME = "xoac_test_reservations";

    public function reset()
    {
        $query = "DROP TABLE " . static::TABLE_NAME;
        $this->getDB()->manipulate($query);
    }
}

/**
 * Testing the Database for Reservations
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @group needsInstalledILIAS
 */
class ReservationDBTest extends TestCase
{
    protected $db;

    protected $obj_id = 1;
    protected $oac_obj_id = 2;
    protected $ses_obj_id = 3;
    protected $usr_id = 4;
    protected $date;
    protected $selfpay = true;

    public function setUp() : void
    {
        include_once('./Services/PHPUnit/classes/class.ilUnitUtil.php');
        \ilUnitUtil::performInitialisation();

        require_once('Services/Calendar/classes/class.ilDate.php');
        $this->date = new ilDateTime("2017-12-24", IL_CAL_DATE);

        global $DIC;
        $this->db = new RMockDB($DIC->database());
        $this->db->createTable();
    }


    public function test_construction()
    {
        $this->assertInstanceOf(DB::class, $this->db);
    }

    public function test_create()
    {
        $r = $this->db->createReservation(
            $this->oac_obj_id,
            $this->usr_id,
            $this->date->get(IL_CAL_DATE),
            $this->ses_obj_id,
            $this->selfpay
        );
        $this->assertInstanceOf(Reservation::class, $r);
    }

    public function test_select()
    {
        $r = $this->db->selectForUserInObject($this->usr_id, $this->oac_obj_id);
        $r = $r[0];
        $this->assertEquals($this->oac_obj_id, $r->getAccomodationObjId());
        $this->assertEquals($this->ses_obj_id, $r->getSessionObjId());
        $this->assertEquals($this->usr_id, $r->getUserId());
        $this->assertEquals($this->date, $r->getDate());
        $this->assertEquals($this->selfpay, $r->getSelfpay());
    }

    public function test_update()
    {
        $usr_id = 666;
        $oac_obj_id = 6667;

        $r = $this->db->createReservation(
            $oac_obj_id,
            $usr_id,
            $this->date->get(IL_CAL_DATE),
            $this->ses_obj_id,
            $this->selfpay
        );

        $r_changed = new Reservation(
            $r->getId(),
            $oac_obj_id,
            $usr_id,
            new ilDateTime('2020-11-27', IL_CAL_DATE),
            1234,
            true
        );
        $this->db->update($r_changed);
        $this->assertEquals(
            $r_changed,
            $this->db->selectForUserInObject($usr_id, $oac_obj_id)[0]
        );
    }

    //not a test, remove table
    public function test_cleanup()
    {
        $this->db->reset();
    }
}
