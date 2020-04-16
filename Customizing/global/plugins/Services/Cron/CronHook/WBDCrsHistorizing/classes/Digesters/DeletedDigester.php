<?php

namespace CaT\Plugins\WBDCrsHistorizing\Digesters;

use CaT\Historization\Digester\Digester as Digester;

class DeletedDigester implements Digester
{
    public function digest(array $payload)
    {
        return ['deleted' => true];
    }
}
