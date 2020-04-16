<?php declare(strict_types=1);

namespace CaT\Plugins\ParticipationsImport\Mappings;

class ParticipationStatusRelationMapping extends Relation implements ParticipationStatusMapping
{
    public function properValues() : array
    {
        return [
            'successful',
            'in_progress',
            'absent'
        ];
    }
    public function ilParticipationStatusForExternStatus(string $extern) : string
    {
        return $this->getRelation($extern);
    }
}
