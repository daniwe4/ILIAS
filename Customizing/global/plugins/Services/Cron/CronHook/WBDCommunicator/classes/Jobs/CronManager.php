<?php

/* Copyright (c) 2019 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

declare(strict_types = 1);

namespace CaT\Plugins\WBDCommunicator\Jobs;

class CronManager
{
    public function ping(string $plugin_id)
    {
        \ilCronManager::ping($plugin_id);
    }
}
