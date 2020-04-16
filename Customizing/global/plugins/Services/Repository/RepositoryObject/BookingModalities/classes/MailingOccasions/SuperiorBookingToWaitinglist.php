<?php
namespace CaT\Plugins\BookingModalities\MailingOccasions;

use ILIAS\TMS\Booking;

class SuperiorBookingToWaitinglist extends MailOccasionBase
{
    const TEMPLATE_IDENT = 'B04';

    protected static $events = array(
        Booking\Actions::EVENT_SUPERIOR_BOOKED_WAITING
    );
}
