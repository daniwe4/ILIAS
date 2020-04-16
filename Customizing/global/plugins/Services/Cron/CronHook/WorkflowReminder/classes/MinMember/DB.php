<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WorkflowReminder\MinMember;

interface DB
{
    /**
     * @throws \Exception
     * @return MinMember[]
     */
    public function getCoursesWithoutMinMembers(int $offset) : array;
}
