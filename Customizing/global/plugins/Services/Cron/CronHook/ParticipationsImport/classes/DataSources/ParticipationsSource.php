<?php declare(strict_types=1);

namespace CaT\Plugins\ParticipationsImport\DataSources;

interface ParticipationsSource
{
    public function getParticipations() : \Generator;
}
