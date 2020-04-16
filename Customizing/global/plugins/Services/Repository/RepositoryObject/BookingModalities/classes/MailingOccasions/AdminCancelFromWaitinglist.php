<?php
namespace CaT\Plugins\BookingModalities\MailingOccasions;

use ILIAS\TMS\Booking;

class AdminCancelFromWaitinglist extends MailOccasionBase
{
    const TEMPLATE_IDENT = 'C06';

    protected static $events = array(
        //Booking\Actions::EVENT_ADMIN_CANCELED_WAITING
    );
}
