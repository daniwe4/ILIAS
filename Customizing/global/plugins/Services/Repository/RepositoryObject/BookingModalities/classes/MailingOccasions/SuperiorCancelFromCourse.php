<?php
namespace CaT\Plugins\BookingModalities\MailingOccasions;

use ILIAS\TMS\Booking;

class SuperiorCancelFromCourse extends MailOccasionBase
{
    const TEMPLATE_IDENT = 'C03';

    protected static $events = array(
        Booking\Actions::EVENT_SUPERIOR_CANCELED_COURSE
    );
}
