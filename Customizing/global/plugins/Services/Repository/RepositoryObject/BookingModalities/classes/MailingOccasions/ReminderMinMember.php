<?php
namespace CaT\Plugins\BookingModalities\MailingOccasions;

use ILIAS\TMS\Booking;

class ReminderMinMember extends MailOccasionBase
{
    const TEMPLATE_IDENT = 'R01';

    protected static $events = array(
        Booking\Actions::EVENT_REMINDER_MIN_MEMBER
    );
}
