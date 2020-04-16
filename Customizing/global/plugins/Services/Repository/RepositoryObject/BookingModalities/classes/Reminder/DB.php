<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\BookingModalities\Reminder;

interface DB
{
    public function insert(bool $send_mail, int $days_before_course, int $usr_id);
    public function select() : MinMember;
}
