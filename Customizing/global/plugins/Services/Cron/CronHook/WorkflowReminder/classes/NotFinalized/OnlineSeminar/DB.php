<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WorkflowReminder\NotFinalized\OnlineSeminar;

interface DB
{
    /**
     * @return NotFinalized[]
     */
    public function getNotFinalizedCourses(string $checkline) : array;
}
