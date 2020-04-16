<?php

namespace CaT\Plugins\BookingModalities\Steps;

use \ILIAS\TMS\Booking;

class SuperiorDuplicateCourseStep extends DuplicateCourseStep implements Booking\SuperiorBookingStep, Booking\SuperiorBookingWithApprovalsStep
{
    /**
     * @inheritdoc
     */
    protected function requiredBookingMode()
    {
        require_once(__DIR__ . "/../Settings/class.ilBookingModalitiesGUI.php");
        return \ilBookingModalitiesGUI::SUPERIOR_BOOKING;
    }

    /**
     * @inheritdoc
     */
    protected function getInfoMessage($days, $usr_id)
    {
        $fullname = \ilObjUser::_lookupFullname($usr_id);
        return sprintf($this->txt("superior_duplicate_courses_confirmation"), $fullname, $days);
    }
}
