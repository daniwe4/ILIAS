<?php
namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use CaT\Historization\Digester\Digester as Digester;

class MemberlistFinalizedDigester implements Digester
{
    public function digest(array $payload)
    {
        if (array_key_exists('finalized_date', $payload)) {
            return ['participation_finalized_date' => $payload['finalized_date']];
        } else {
            return ['participation_finalized_date' => date('Y-m-d')];
        }
    }
}
