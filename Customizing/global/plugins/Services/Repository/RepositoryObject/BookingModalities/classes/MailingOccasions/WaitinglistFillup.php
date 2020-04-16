<?php
namespace CaT\Plugins\BookingModalities\MailingOccasions;

use ILIAS\TMS\Booking;

class WaitinglistFillup extends MailOccasionBase
{
    const TEMPLATE_IDENT = 'B07';

    protected static $events = array(
        Booking\Actions::EVENT_USER_FILLEDUP_FROM_WAITING
    );
}
