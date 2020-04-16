<?php
namespace CaT\Plugins\BookingModalities\MailingOccasions;

use ILIAS\TMS\Booking;

class SelfCancelFromCourse extends MailOccasionBase
{
    const TEMPLATE_IDENT = 'C01';

    protected static $events = array(
        Booking\Actions::EVENT_USER_CANCELED_COURSE
    );
}
