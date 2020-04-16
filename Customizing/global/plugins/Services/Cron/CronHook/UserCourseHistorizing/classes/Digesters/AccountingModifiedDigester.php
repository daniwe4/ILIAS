<?php

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use CaT\Historization\Digester\Digester as Digester;

class AccountingModifiedDigester implements Digester
{
    public function digest(array $payload)
    {
        $return = [];
        $acc = $payload['xacc'];
        $return['crs_id'] = $acc->getParentCourse()->getId();
        $actions = $acc->getObjectActions();
        $return['net_total_cost'] = $actions->getNetSum();
        $return['gross_total_cost'] = $actions->getGrossSum();
        $return['costcenter_finalized'] = $acc->getSettings()->getFinalized();
        $return['fee'] = $actions->getFeeActions()->select()->getFee();
        $return['max_cancellation_fee'] = $actions->getCancellationFeeActions()->select()->getCancellationFee();

        return $return;
    }
}
