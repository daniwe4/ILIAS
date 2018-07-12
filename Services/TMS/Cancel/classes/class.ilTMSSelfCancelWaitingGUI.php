<?php
/**
 * cat-tms-patch start
 */

use ILIAS\TMS\Booking;

require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("Services/TMS/Cancel/classes/ilTMSCancelGUI.php");

/**
 * Displays the TMS cancel
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilTMSSelfCancelWaitingGUI extends \ilTMSCancelGUI
{
    /**
     * @inheritdocs
     */
    protected function getComponentClass()
    {
        return Booking\SelfBookingStep::class;
    }

    /**
     * @inheritdocs
     */
    protected function setParameter($crs_ref_id, $usr_id)
    {
        $this->g_ctrl->setParameterByClass("ilTMSSelfCancelWaitingGUI", "crs_ref_id", $crs_ref_id);
        $this->g_ctrl->setParameterByClass("ilTMSSelfCancelWaitingGUI", "usr_id", $usr_id);
    }

    /**
     * @inheritdoc
     */
    protected function callOnFinish($acting_usr_id, $target_usr_id, $crs_ref_id)
    {
        $event = Booking\Actions::EVENT_USER_CANCELED_WAITING;
        $this->fireBookingEvent($event, $target_usr_id, $crs_ref_id);
    }

    protected function userHasBookingState($crs_ref_id, $usr_id)
    {
        $crs_id = ilObject::_lookupObjId($crs_ref_id);
        return ilWaitingList::_isOnList($usr_id, $crs_id);
    }

    protected function getBookingStateMessage($crs_ref_id, $usr_id)
    {
        return "user_not_booked_on_waiting";
    }
}

/**
 * cat-tms-patch end
 */
