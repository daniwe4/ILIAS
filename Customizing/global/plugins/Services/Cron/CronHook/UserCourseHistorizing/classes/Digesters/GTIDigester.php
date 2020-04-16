<?php

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use CaT\Historization\Digester\Digester as Digester;

class GTIDigester implements Digester
{
    public function digest(array $payload)
    {
        $return = [];
        if (array_key_exists('minutes', $payload)) {
            $return['gti_learning_time'] = $payload['minutes'];
        }
        return $return;
    }
}
