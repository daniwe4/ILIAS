<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\BookingAcknowledge;

/**
 *
 *
 */
interface BookingAcknowledge
{
    const PLUGIN_ID = 'xack';
    const PLUGIN_NAME = 'BookingAcknowledge';

    const ORGU_CONTEXT = 'xack';
    const ORGU_OP_SEE_USERBOOKINGS = 'orgu_see_userbookings';
    const ORGU_OP_ACKNOWLEDGE = 'orgu_acknowledge_userbookings';

    const OP_ACKNOWLEDGE = 'acknowledge_userbookings';
}
