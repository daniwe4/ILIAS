<?php
namespace CaT\Plugins\BookingModalities\MailingOccasions;

use ILIAS\TMS\Booking;

class AutoCancelFromWaitinglist extends MailOccasionBase
{
    const TEMPLATE_IDENT = 'C07';

    protected static $events = array(
        Booking\Actions::EVENT_AUTO_CANCELED_WAITING
    );
}
