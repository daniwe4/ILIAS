<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WBDManagement\GutBeraten;

class WBDData
{
    /**
     * @var int
     */
    protected $usr_id;

    /**
     * @var string
     */
    protected $wbd_id;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var \DateTime
     */
    protected $approve_date;

    public function __construct(
        int $usr_id,
        string $wbd_id,
        string $status,
        \DateTime $approve_date
) {
        $this->usr_id = $usr_id;
        $this->wbd_id = $wbd_id;
        $this->status = $status;
        $this->approve_date = $approve_date;
    }

    public function getUsrId() : int
    {
        return $this->usr_id;
    }

    public function getWbdId() : string
    {
        return $this->wbd_id;
    }

    public function getStatus() : string
    {
        return $this->status;
    }

    public function getApproveDate() : \DateTime
    {
        return $this->approve_date;
    }
}
