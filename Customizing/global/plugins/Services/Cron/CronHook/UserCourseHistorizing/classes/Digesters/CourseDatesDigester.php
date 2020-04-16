<?php

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use CaT\Historization\Digester\Digester as Digester;

class CourseDatesDigester implements Digester
{
    public function digest(array $payload)
    {
        $return = [];
        if ($payload['object']) {
            $crs = $payload['object'];
            $return = ['begin_date' => '0001-01-01', 'end_date' => '0001-01-01'];
            $crs_start = $crs->getCourseStart();
            if ($crs_start instanceof \ilDate) {
                $return['begin_date'] = $crs_start->get(IL_CAL_DATE);
            }
            $crs_end = $crs->getCourseEnd();
            if ($crs_end instanceof \ilDate) {
                $return['end_date'] = $crs_end->get(IL_CAL_DATE);
            }
        }
        return $return;
    }
}
