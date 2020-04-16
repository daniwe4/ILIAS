<?php
namespace CaT\Plugins\BookingModalities\MailingOccasions;

use ILIAS\TMS\Booking;

class CancelFromWaitinglistAfterBooking extends MailOccasionBase
{
    const TEMPLATE_IDENT = 'C08';

    protected static $events = array(
        Booking\Actions::EVENT_CANCELED_WAITING_AFTER_BOOKING
    );
}
