<?php

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use CaT\Historization\Digester\Digester as Digester;

class CreatedTSDigester implements Digester
{
    public function digest(array $payload)
    {
        return ['created_ts' => time()];
    }
}
