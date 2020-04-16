<?php

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use CaT\Historization\Digester\Digester as Digester;

class AccountingDeletedDigester implements Digester
{
    public function digest(array $payload)
    {
        $return = [];
        $return['crs_id'] = $payload['xacc']->getParentCourse()->getId();
        $return['net_total_cost'] = 0.0;
        $return['gross_total_cost'] = 0.0;
        $return['costcenter_finalized'] = null;
        return $return;
    }
}
