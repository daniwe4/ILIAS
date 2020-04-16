<?php declare(strict_types=1);

namespace CaT\Plugins\ParticipationsImport\Mappings;

interface BookingStatusMapping
{
    public function ilBookingStatusForExternStatus(string $extern) : string;
}
