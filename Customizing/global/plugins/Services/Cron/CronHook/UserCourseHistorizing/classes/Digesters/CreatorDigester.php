<?php

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use CaT\Historization\Digester\Digester as Digester;

class CreatorDigester implements Digester
{
    public function digest(array $payload)
    {
        global $DIC;
        return ['creator' => $DIC['ilUser']->getId()];
    }
}
