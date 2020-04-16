<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WorkflowReminder\MinMember;

class MinMember
{
    /**
     * @var int
     */
    protected $crs_ref_id;

    /**
     * @var \DateTime
     */
    protected $begin_date;

    /**
     * @var int
     */
    protected $child_ref_id;

    /**
     * @var int
     */
    protected $min_member;

    public function __construct(
        int $crs_ref_id,
        \DateTime $begin_date,
        int $child_ref_id,
        int $min_member
    ) {
        $this->crs_ref_id = $crs_ref_id;
        $this->begin_date = $begin_date;
        $this->child_ref_id = $child_ref_id;
        $this->min_member = $min_member;
    }

    public function getCrsRefId() : int
    {
        return $this->crs_ref_id;
    }

    public function getBeginDate() : \DateTime
    {
        return $this->begin_date;
    }

    public function getChildRefId() : int
    {
        return $this->child_ref_id;
    }

    public function getMinMember() : int
    {
        return $this->min_member;
    }
}
