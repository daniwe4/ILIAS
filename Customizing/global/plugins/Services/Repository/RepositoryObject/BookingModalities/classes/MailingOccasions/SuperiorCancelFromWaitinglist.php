<?php
namespace CaT\Plugins\BookingModalities\MailingOccasions;

use ILIAS\TMS\Booking;

class SuperiorCancelFromWaitinglist extends MailOccasionBase
{
    const TEMPLATE_IDENT = 'C04';

    protected static $events = array(
        Booking\Actions::EVENT_SUPERIOR_CANCELED_WAITING
    );
}
