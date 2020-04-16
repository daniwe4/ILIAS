<?php
use ILIAS\TMS\Booking;
use ILIAS\TMS\Wizard;
use CaT\Plugins\BookingApprovals\Booking\ilBookingWithApproveGUI;

require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
require_once __DIR__ . "/ilBookingWithApproveGUI.php";

/**
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilSuperiorBookingWithApproveGUI extends ilBookingWithApproveGUI
{
    /**
     * @inheritdocs
     */
    protected function getComponentClass()
    {
        return Booking\SuperiorBookingWithApprovalsStep::class;
    }

    /**
     * @inheritdocs
     */
    protected function getConfirmButtonLabel()
    {
        return $this->g_lng->txt("superior_book_with_approval_confirm");
    }

    /**
     * @inheritdoc
     */
    protected function setParameter($crs_ref_id, $usr_id)
    {
        assert('is_int($crs_ref_id) || is_null($crs_ref_id)');
        assert('is_int($usr_id) || is_null($usr_id)');

        $this->g_ctrl->setParameterByClass("ilSuperiorBookingWithApproveGUI", "crs_ref_id", $crs_ref_id);
        $this->g_ctrl->setParameterByClass("ilSuperiorBookingWithApproveGUI", "usr_id", $usr_id);
    }

    /**
     * @inheritdoc
     */
    protected function getDuplicatedCourseMessage($usr_id)
    {
        return array(sprintf($this->g_lng->txt("superior_duplicate_course_booked"), ilObjUser::_lookupFullname($usr_id)));
    }

    /**
     * @inheritdoc
     */
    protected function getTranslations()
    {
        $trans = new \ILIAS\TMS\TranslationsImpl(
            array(
                ILIAS\TMS\Wizard\Player::TXT_TITLE => $this->getPlayerTitle(),
            ),
            parent::getTranslations()
        );
        return $trans;
    }

    /**
     * Get title of player (by mixing in the user's name).
     *
     * @return string
     */
    protected function getPlayerTitle()
    {
        assert('is_numeric($_GET["usr_id"])');
        $usr_id = (int) $_GET["usr_id"];

        require_once("Services/User/classes/class.ilObjUser.php");
        return sprintf($this->g_lng->txt("booking_with_approval_for"), ilObjUser::_lookupFullname($usr_id));
    }
}
