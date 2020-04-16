<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\AutomaticCancelWaitinglist\Database;

class CourseData
{
    /**
     * @var int
     */
    protected $crs_ref_id;

    /**
     * @var DateTime
     */
    protected $begin_date;

    /**
     * @var mixed[]
     */
    protected $modalities_infos;

    public function __construct(int $crs_ref_id, \DateTime $begin_date, int $xbkm_ref_id, int $cancellation)
    {
        $this->crs_ref_id = $crs_ref_id;
        $this->begin_date = $begin_date;
        $this->modalities_infos = [
            "xbkm_ref_id" => $xbkm_ref_id,
            "cancellation" => $cancellation
        ];
    }

    public function getCrsRefId() : int
    {
        return $this->crs_ref_id;
    }

    public function getBeginDate() : \DateTime
    {
        return $this->begin_date;
    }

    public function getModalitiesInfos() : array
    {
        return $this->modalities_infos;
    }
}
