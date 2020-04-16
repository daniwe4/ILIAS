<?php declare(strict_types=1);

namespace CaT\Plugins\ParticipationsImport\Mappings;

interface ParticipationStatusMapping
{
    public function ilParticipationStatusForExternStatus(string $extern) : string;
}
