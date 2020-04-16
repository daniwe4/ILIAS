<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\BookingAcknowledge\Utils;

/**
 *
 */
class UserCourseRelation
{
    /**
     * @var int
     */
    protected $usr_id;

    /**
     * @var int
     */
    protected $crs_ref_id;

    public function __construct(
        int $usr_id,
        int $crs_ref_id
    ) {
        $this->usr_id = $usr_id;
        $this->crs_ref_id = $crs_ref_id;
    }

    public function getUserId() : int
    {
        return $this->usr_id;
    }

    public function getCourseRefId() : int
    {
        return $this->crs_ref_id;
    }
}
