<?php

use ILIAS\TMS\Booking;

require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("Services/TMS/Booking/classes/ilTMSBookingGUI.php");

/**
 * Displays the TMS self booking
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilTMSSelfBookingGUI extends \ilTMSBookingGUI
{
    /**
     * @inheritdocs
     */
    protected function getComponentClass()
    {
        return Booking\SelfBookingStep::class;
    }

    /**
     * @inheritdoc
     */
    protected function setParameter($crs_ref_id, $usr_id)
    {
        assert('is_int($crs_ref_id) || is_null($crs_ref_id)');
        assert('is_int($usr_id) || is_null($usr_id)');

        $this->g_ctrl->setParameterByClass("ilTMSSelfBookingGUI", "crs_ref_id", $crs_ref_id);
        $this->g_ctrl->setParameterByClass("ilTMSSelfBookingGUI", "usr_id", $usr_id);
    }

    /**
     * @inheritdoc
     */
    protected function callOnFinish($acting_usr_id, $target_usr_id, $crs_ref_id)
    {
        $event = Booking\Actions::EVENT_USER_BOOKED_COURSE;
        $this->fireBookingEvent($event, $target_usr_id, $crs_ref_id);
    }

    protected function userHasBookingState($crs_ref_id, $usr_id)
    {
        $crs = new ilObjCourse($crs_ref_id, true);
        return $crs->getMembersObject()->isMember($usr_id);
    }

    protected function getBookingStateMessage($crs_ref_id, $usr_id)
    {
        return "user_booked_on_course";
    }
}
