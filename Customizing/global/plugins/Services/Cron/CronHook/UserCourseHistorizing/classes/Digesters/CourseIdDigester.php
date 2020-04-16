<?php

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use CaT\Historization\Digester\Digester as Digester;

class CourseIdDigester implements Digester
{
    public function digest(array $payload)
    {
        $return = [];
        if (!isset($payload['crs_id'])) {
            if (isset($payload['object'])) {
                $return['crs_id'] = $payload['object']->getId();
            } elseif (isset($payload['obj_id'])) {
                $return['crs_id'] = $payload['obj_id'];
            } elseif (isset($payload['xoac_parent_crs_info'])) {
                $return['crs_id'] = $payload['xoac_parent_crs_info']['obj_id'];
            } elseif (isset($payload['crs_obj_id'])) {
                $return['crs_id'] = (int) $payload['crs_obj_id'];
            }
        }
        return $return;
    }
}
