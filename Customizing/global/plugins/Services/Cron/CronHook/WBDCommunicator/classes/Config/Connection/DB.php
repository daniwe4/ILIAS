<?php

/* Copyright (c) 2019 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

declare(strict_types = 1);

namespace CaT\Plugins\WBDCommunicator\Config\Connection;

interface DB
{
    public function saveConnection(Connection $connection);
    public function getConnection() : Connection;
}
