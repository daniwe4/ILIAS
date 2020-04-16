<?php

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use CaT\Historization\Digester\Digester as Digester;

class UserCancellationFeeDigester implements Digester
{
    public function digest(array $payload)
    {
        $return = [];
        if (array_key_exists('cancellationfee', $payload)) {
            $return['cancellation_fee'] = $payload['cancellationfee'];
        }
        return $return;
    }
}
