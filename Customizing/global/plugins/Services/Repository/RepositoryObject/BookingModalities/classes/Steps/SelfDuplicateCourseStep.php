<?php

namespace CaT\Plugins\BookingModalities\Steps;

use \ILIAS\TMS\Booking;

class SelfDuplicateCourseStep extends DuplicateCourseStep implements Booking\SelfBookingStep, Booking\SelfBookingWithApprovalsStep
{
    /**
     * @inheritdoc
     */
    protected function requiredBookingMode()
    {
        require_once(__DIR__ . "/../Settings/class.ilBookingModalitiesGUI.php");
        return \ilBookingModalitiesGUI::SELF_BOOKING;
    }
}
