<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WorkflowReminder\NotFinalized\Webinar;

class NotFinalized
{
    /**
     * @var int
     */
    protected $crs_ref_id;

    /**
     * @var int
     */
    protected $child_ref_id;

    public function __construct(int $crs_ref_id, int $child_ref_id)
    {
        $this->crs_ref_id = $crs_ref_id;
        $this->child_ref_id = $child_ref_id;
    }

    public function getCrsRefId() : int
    {
        return $this->crs_ref_id;
    }

    public function getChildRefId() : int
    {
        return $this->child_ref_id;
    }
}
