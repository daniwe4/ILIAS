<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WorkflowReminder\NotFinalized\Log;

interface DB
{
    public function insert(int $crs_ref_id, int $child_ref_id, \DateTime $date);
}
