<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\Agenda\Settings;

use DateTime;

class Settings
{
    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var DateTime | null
     */
    protected $start_time;

    public function __construct(int $obj_id, DateTime $start_time = null)
    {
        $this->obj_id = $obj_id;
        $this->start_time = $start_time;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    /**
     * @return DateTime | null
     */
    public function getStartTime()
    {
        return $this->start_time;
    }

    public function withStartTime(DateTime $start_time)
    {
        $clone = clone $this;
        $clone->start_time = $start_time;
        return $clone;
    }
}
