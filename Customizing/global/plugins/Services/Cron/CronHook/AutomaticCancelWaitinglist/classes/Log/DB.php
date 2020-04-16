<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\AutomaticCancelWaitinglist\Log;

interface DB
{
    public function logSuccess(int $crs_ref_id, string $today);
    public function logFail(int $crs_ref_id, string $today, string $message);
    /**
     * @return Entry[]
     */
    public function getSuccessLogEntries() : array;
    /**
     * @return Entry[]
     */
    public function getFailedLogEntries() : array;
}
