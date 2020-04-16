<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\Agenda\EntryChecks;

use DateTime;

class CheckObject
{
    /**
     * @var int
     */
    protected $duration;

    /**
     * @var string
     */
    protected $pool_item_id;

    public function __construct(
        int $duration,
        string $pool_item_id
    ) {
        $this->duration = $duration;
        $this->pool_item_id = $pool_item_id;
    }

    public function getDuration() : int
    {
        return $this->duration;
    }

    public function getPoolItemId() : string
    {
        return $this->pool_item_id;
    }
}
