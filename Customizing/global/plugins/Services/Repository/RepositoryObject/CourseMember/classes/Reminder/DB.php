<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\CourseMember\Reminder;

interface DB
{
    public function insert(int $interval, int $usr_id);
    public function select() : NotFinalized;
}
