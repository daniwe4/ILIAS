<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WorkflowReminder\NotFinalized\CourseMember;

interface DB
{
    /**
     * @return NotFinalized[]
     */
    public function getNotFinalizedCourses(string $due_date) : array;
}
