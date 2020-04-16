<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\Webinar\Config\Reminder;

class NotFinalized
{
    /**
     * @var int
     */
    protected $interval;

    public function __construct(int $interval)
    {
        $this->interval = $interval;
    }

    public function getInterval() : int
    {
        return $this->interval;
    }
}
