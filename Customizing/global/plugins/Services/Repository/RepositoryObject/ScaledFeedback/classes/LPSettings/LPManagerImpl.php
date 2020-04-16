<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */
/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\ScaledFeedback\LPSettings;

include_once("Services/Tracking/classes/class.ilLPStatusWrapper.php");

class LPManagerImpl implements LPManager
{
    /**
     * @inheritdoc
     */
    public function refresh(int $obj_id)
    {
        \ilLPStatusWrapper::_refreshStatus($obj_id);
    }

    /**
     * @inheritdoc
     */
    public function coursePassed(int $needed, array $lp_data) : bool
    {
        if ($lp_data["passed"] === null && $lp_data["minutes"] >= $needed) {
            return true;
        } else {
            if ((bool) $lp_data["passed"]) {
                return true;
            }
        }

        return false;
    }
}
