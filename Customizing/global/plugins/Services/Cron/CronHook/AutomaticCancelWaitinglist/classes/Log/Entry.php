<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\AutomaticCancelWaitinglist\Log;

class Entry
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $crs_ref_id;
    /**
     * @var \DateTime
     */
    protected $date;
    /**
     * @var string
     */
    protected $message;

    public function __construct(int $id, int $crs_ref_id, \DateTime $date, string $message)
    {
        $this->id = $id;
        $this->crs_ref_id = $crs_ref_id;
        $this->date = $date;
        $this->message = $message;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCrsRefId()
    {
        return $this->crs_ref_id;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getMessage()
    {
        return $this->message;
    }
}
