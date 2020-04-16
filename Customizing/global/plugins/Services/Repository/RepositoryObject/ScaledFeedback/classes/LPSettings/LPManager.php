<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */
/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\ScaledFeedback\LPSettings;

interface LPManager
{
    /**
     * Refresh the lp state
     */
    public function refresh(int $obj_id);

    /**
     * Check user has passed the course
     * @param string[] 	$lp_data
     */
    public function coursePassed(int $needed, array $lp_data) : bool;
}
