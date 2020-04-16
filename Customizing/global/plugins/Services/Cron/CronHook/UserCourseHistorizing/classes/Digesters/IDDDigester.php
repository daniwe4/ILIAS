<?php

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use CaT\Historization\Digester\Digester as Digester;

class IDDDigester implements Digester
{
    public function digest(array $payload)
    {
        $return = [];
        if (array_key_exists('minutes', $payload)) {
            $return['idd_learning_time'] = $payload['minutes'];
        }
        if (array_key_exists('lp_value', $payload)) {
            $return['custom_p_status'] = $payload['lp_value'];
        }
        return $return;
    }
}
