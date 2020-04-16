<?php declare(strict_types=1);

namespace CaT\Plugins\ParticipationsImport\DataSources;

interface ConfigStorage
{
    public function loadCurrentConfig() : Config;
    public function storeConfigAsCurrent(Config $cfg);
}
