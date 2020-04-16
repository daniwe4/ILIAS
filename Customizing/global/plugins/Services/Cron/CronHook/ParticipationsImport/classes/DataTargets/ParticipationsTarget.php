<?php declare(strict_types=1);

namespace CaT\Plugins\ParticipationsImport\DataTargets;

interface ParticipationsTarget
{
    public function importParticipation(Participation $participation);
}
