<?php
namespace CaT\Plugins\BookingModalities\MailingOccasions;

use ILIAS\TMS\Booking;

class AdminCancelFromCourse extends MailOccasionBase
{
    const TEMPLATE_IDENT = 'C05';

    protected static $events = array(
        //Booking\Actions::EVENT_ADMIN_CANCELED_COURSE
    );
}
