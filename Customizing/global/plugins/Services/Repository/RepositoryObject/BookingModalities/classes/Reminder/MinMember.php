<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\BookingModalities\Reminder;

class MinMember
{
    /**
     * @var bool
     */
    protected $send_mail;

    /**
     * @var int
     */
    protected $days_before_course;

    public function __construct(bool $send_mail, int $days_before_course)
    {
        $this->send_mail = $send_mail;
        $this->days_before_course = $days_before_course;
    }

    public function getSendMail() : bool
    {
        return $this->send_mail;
    }

    public function getDaysBeforeCourse() : int
    {
        return $this->days_before_course;
    }
}
