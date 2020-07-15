<?php

use ILIAS\TMS\Booking;

require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("Services/TMS/Booking/classes/ilTMSBookingGUI.php");

/**
 * Displays the TMS self booking
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilTMSSelfBookWaitingGUI extends \ilTMSBookingGUI
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
    protected function getConfirmButtonLabel()
    {
        return $this->g_lng->txt("book_waiting_confirm");
    }

    /**
     * @inheritdocs
     */
    protected function setParameter($crs_ref_id, $usr_id)
    {
        assert('is_int($crs_ref_id) || is_null($crs_ref_id)');
        assert('is_int($usr_id) || is_null($usr_id)');

        $this->g_ctrl->setParameterByClass("ilTMSSelfBookWaitingGUI", "crs_ref_id", $crs_ref_id);
        $this->g_ctrl->setParameterByClass("ilTMSSelfBookWaitingGUI", "usr_id", $usr_id);
    }

    /**
     * @inheritdoc
     */
    protected function getTranslations()
    {
        $trans = new \ILIAS\TMS\TranslationsImpl(
            array(
                ILIAS\TMS\Wizard\Player::TXT_TITLE => $this->g_lng->txt("booking_waiting")
            ),
            parent::getTranslations()
        );
        return $trans;
    }

    /**
     * @inheritdoc
     */
    protected function callOnFinish($acting_usr_id, $target_usr_id, $crs_ref_id)
    {
        $event = Booking\Actions::EVENT_USER_BOOKED_WAITING;
        $this->fireBookingEvent($event, $target_usr_id, $crs_ref_id);
    }

    protected function userHasBookingState($crs_ref_id, $usr_id)
    {
        $crs_id = ilObject::_lookupObjId($crs_ref_id);
        return ilWaitingList::_isOnList($usr_id, $crs_id);
    }

    protected function getBookingStateMessage($crs_ref_id, $usr_id)
    {
        return "user_booked_on_waiting";
    }
}
