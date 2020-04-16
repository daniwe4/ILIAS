<?php

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use CaT\Historization\Digester\Digester as Digester;

class UserIdDigester implements Digester
{
    public function digest(array $payload)
    {
        $return = [];
        if (!isset($payload['usr_id'])) {
            if (isset($payload['xoac_usr_id'])) {
                $return['usr_id'] = $payload['xoac_usr_id'];
            }
        }
        return $return;
    }
}
