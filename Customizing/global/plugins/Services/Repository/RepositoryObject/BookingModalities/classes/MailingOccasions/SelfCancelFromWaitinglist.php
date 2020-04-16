<?php
namespace CaT\Plugins\BookingModalities\MailingOccasions;

use ILIAS\TMS\Booking;

class SelfCancelFromWaitinglist extends MailOccasionBase
{
    const TEMPLATE_IDENT = 'C02';

    protected static $events = array(
        Booking\Actions::EVENT_USER_CANCELED_WAITING
    );
}
