<?php

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use CaT\Historization\Digester\Digester as Digester;

class TitleDigester implements Digester
{
    public function digest(array $payload)
    {
        $return = [];
        if ($payload['object']) {
            $obj = $payload['object'];
            $return['title'] = $obj->getTitle();
        }
        return $return;
    }
}
