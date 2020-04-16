<?php
namespace CaT\Plugins\BookingModalities\MailingOccasions;

use ILIAS\TMS\Booking;

class AdminBookingToWaitinglist extends MailOccasionBase
{
    const TEMPLATE_IDENT = 'B06';

    protected static $events = array(
        //Booking\Actions::EVENT_ADMIN_BOOKED_WAITING
    );
}
