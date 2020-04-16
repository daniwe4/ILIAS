<?php

declare(strict_types=1);

namespace CaT\Plugins\WBDCommunicator\Config\WBD;

interface DB
{
    public function saveActiveWBDSystem(string $system);
    public function getActiveWBDSystem() : System;
}
