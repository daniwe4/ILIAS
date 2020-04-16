<?php
namespace CaT\Plugins\BookingModalities\MailingOccasions;

use ILIAS\TMS\Booking;

class SelfBookingToWaitinglist extends MailOccasionBase
{
    const TEMPLATE_IDENT = 'B02';

    protected static $events = array(
        Booking\Actions::EVENT_USER_BOOKED_WAITING
    );
}
