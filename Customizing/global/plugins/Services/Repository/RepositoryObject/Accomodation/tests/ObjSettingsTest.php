<?php
use CaT\Plugins\Accomodation\ObjSettings\ObjSettings;
use PHPUnit\Framework\TestCase;

/**
 * Testing the ObjSettings
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ObjSettingsTest extends TestCase
{
    protected $obj_id = 1;
    protected $location_obj_id = 2;
    protected $use_location_from_course = false;
    protected $allow_prior_day = false;
    protected $allow_following_day = true;
    protected $booking_end = 5;
    protected $mailing_use_venue_settings = true;
    protected $recipient = 'someone@mail.com';
    protected $send_days_before = 6;

    public function test_construction()
    {
        $oset = new ObjSettings(
            $this->obj_id
        );
        $this->assertInstanceOf(ObjSettings::class, $oset);
        $this->assertEquals($this->obj_id, $oset->getObjId());
        return $oset;
    }

    /**
     * @depends test_construction
     */
    public function testSettersAndGetters($oset)
    {
        $oset = $oset->withLocationObjId($this->location_obj_id);
        $this->assertEquals($this->location_obj_id, $oset->getLocationObjId());

        $oset = $oset->withLocationFromCourse($this->use_location_from_course);
        $this->assertEquals($this->use_location_from_course, $oset->getLocationFromCourse());

        $oset = $oset->withPriorDayAllowed($this->allow_prior_day);
        $this->assertEquals($this->allow_prior_day, $oset->isPriorDayAllowed());

        $oset = $oset->withFollowingDayAllowed($this->allow_following_day);
        $this->assertEquals($this->allow_following_day, $oset->isFollowingDayAllowed());

        $oset = $oset->withBookingEnd($this->booking_end);
        $this->assertEquals($this->booking_end, $oset->getBookingEnd());

        $oset = $oset->withMailsettingsFromVenue($this->mailing_use_venue_settings);
        $this->assertEquals($this->mailing_use_venue_settings, $oset->getMailsettingsFromVenue());

        $oset = $oset->withMailRecipient($this->recipient);
        $this->assertEquals($this->recipient, $oset->getMailRecipient());

        $oset = $oset->withSendDaysBefore($this->send_days_before);
        $this->assertEquals($this->send_days_before, $oset->getSendDaysBefore());
    }
}
