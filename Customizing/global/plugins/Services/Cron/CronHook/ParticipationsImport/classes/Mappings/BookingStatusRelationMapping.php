<?php declare(strict_types=1);

namespace CaT\Plugins\ParticipationsImport\Mappings;

class BookingStatusRelationMapping extends Relation implements BookingStatusMapping
{
    public function properValues() : array
    {
        return [
            'participant',
            'cancelled',
            'cancelled_after_deadline',
            'waiting',
            'waiting_cancelled',
            'waiting_self_cancelled',
            'approval_pending',
            'approval_declined'
        ];
    }

    public function ilBookingStatusForExternStatus(string $extern) : string
    {
        return $this->getRelation($extern);
    }
}
