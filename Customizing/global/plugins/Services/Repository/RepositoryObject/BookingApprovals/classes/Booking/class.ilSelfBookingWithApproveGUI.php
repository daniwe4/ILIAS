<?php
use ILIAS\TMS\Booking;
use ILIAS\TMS\Wizard;
use CaT\Plugins\BookingApprovals\Booking\ilBookingWithApproveGUI;

require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
require_once __DIR__ . "/ilBookingWithApproveGUI.php";

/**
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilSelfBookingWithApproveGUI extends ilBookingWithApproveGUI
{
    /**
     * @inheritdocs
     */
    protected function getComponentClass()
    {
        return Booking\SelfBookingWithApprovalsStep::class;
    }

    /**
     * @inheritdocs
     */
    protected function getConfirmButtonLabel()
    {
        return $this->g_lng->txt("book_with_approval_confirm");
    }

    /**
     * @inheritdoc
     */
    protected function setParameter($crs_ref_id, $usr_id)
    {
        assert('is_int($crs_ref_id) || is_null($crs_ref_id)');
        assert('is_int($usr_id) || is_null($usr_id)');

        $this->g_ctrl->setParameterByClass("ilSelfBookingWithApproveGUI", "crs_ref_id", $crs_ref_id);
        $this->g_ctrl->setParameterByClass("ilSelfBookingWithApproveGUI", "usr_id", $usr_id);
    }

    /**
     * @inheritdoc
     */
    protected function getTranslations()
    {
        $trans = new \ILIAS\TMS\TranslationsImpl(
            array(
                ILIAS\TMS\Wizard\Player::TXT_TITLE => $this->g_lng->txt("booking_with_approve")
            ),
            parent::getTranslations()
        );
        return $trans;
    }
}
