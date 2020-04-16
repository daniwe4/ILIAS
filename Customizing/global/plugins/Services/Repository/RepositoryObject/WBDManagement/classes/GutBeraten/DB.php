<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WBDManagement\GutBeraten;

interface DB
{
    public function saveWBDData(int $usr_id, string $wbd_id, string $status);

    /**
     * @param int $usr_id
     * @return WBDData | null
     */
    public function selectFor(int $usr_id);
}
