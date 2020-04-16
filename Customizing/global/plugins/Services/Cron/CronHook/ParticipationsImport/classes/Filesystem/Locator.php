<?php declare(strict_types=1);

namespace CaT\Plugins\ParticipationsImport\Filesystem;

interface Locator
{
    public function getCurrentFilePath() : string;
}
