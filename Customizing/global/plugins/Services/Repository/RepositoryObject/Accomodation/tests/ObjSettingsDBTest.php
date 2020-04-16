<?php
use CaT\Plugins\Accomodation\ObjSettings\ObjSettings;
use CaT\Plugins\Accomodation\ObjSettings\DB;
use CaT\Plugins\Accomodation\ObjSettings\ilDB;
use PHPUnit\Framework\TestCase;

class SMockDB extends ilDB
{
    const TABLE_NAME = "xoac_test_objects";
    const TABLE_NAME_RELEVANT_SESSIONS = "xoac_test_sessions";

    public function reset()
    {
        $query = "DROP TABLE " . static::TABLE_NAME;
        $this->getDB()->manipulate($query);
    }
}

/**
 * Testing the Database for ObjSettings
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @group needsInstalledILIAS
 */
class ObjSettingsDBTest extends TestCase
{
    public $db;

    protected $obj_id = 1;
    protected $location_obj_id = 2;
    protected $use_location_from_course = false;
    protected $allow_prior_day = false;
    protected $allow_following_day = true;
    protected $booking_end = 5;
    protected $mailing_use_venue_settings = true;
    protected $recipient = 'someone@mail.com';
    protected $send_days_before = 6;

    public function setUp() : void
    {
        include_once('./Services/PHPUnit/classes/class.ilUnitUtil.php');
        \ilUnitUtil::performInitialisation();
        global $DIC;
        $this->db = new SMockDB($DIC->database());
        $this->db->createTable();
        $this->db->update1();
        $this->db->update2();
    }


    public function test_construction()
    {
        $this->assertInstanceOf(DB::class, $this->db);
    }

    public function test_create()
    {
        $oset = $this->db->create(
            $this->obj_id,
            $this->location_obj_id,
            $this->use_location_from_course,
            $this->allow_prior_day,
            $this->allow_following_day,
            $this->booking_end,
            $this->mailing_use_venue_settings,
            $this->recipient,
            $this->send_days_before
        );
        $this->assertInstanceOf(ObjSettings::class, $oset);
    }

    public function test_select()
    {
        $oset = $this->db->selectFor($this->obj_id);
        $this->assertEquals($this->obj_id, $oset->getObjId());
        $this->assertEquals($this->location_obj_id, $oset->getLocationObjId());
        $this->assertEquals($this->use_location_from_course, $oset->getLocationFromCourse());
        $this->assertEquals($this->allow_prior_day, $oset->isPriorDayAllowed());
        $this->assertEquals($this->allow_following_day, $oset->isFollowingDayAllowed());
        $this->assertEquals($this->booking_end, $oset->getBookingEnd());
        $this->assertEquals($this->mailing_use_venue_settings, $oset->getMailsettingsFromVenue());
        $this->assertEquals($this->recipient, $oset->getMailRecipient());
        $this->assertEquals($this->send_days_before, $oset->getSendDaysBefore());
    }

    public function test_update()
    {
        $oset_changed = new ObjSettings($this->obj_id, 20, 21, true, false, 22, [4,5,6], false, '', 2);
        $this->db->update($oset_changed);

        $this->assertEquals(
            $oset_changed,
            $this->db->selectFor($this->obj_id)
        );
    }

    public function test_delete()
    {
        $this->db->deleteFor($this->obj_id);
        try {
            $oset = $this->db->selectFor($this->obj_id);
            $this->assertFalse("This should not happen");
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
    }

    //not a test, remove table
    public function test_cleanup()
    {
        $this->db->reset();
    }
}
