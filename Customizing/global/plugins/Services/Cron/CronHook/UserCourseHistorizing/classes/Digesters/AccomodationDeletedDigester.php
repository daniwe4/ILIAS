<?php

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use CaT\Historization\Digester\Digester as Digester;

class AccomodationDeletedDigester implements Digester
{
    public function digest(array $payload)
    {
        $return = array();
        if (array_key_exists('xoac_venue', $payload)) {
            $return['accomodation'] = $payload['xoac_venue'];
        }
        if (array_key_exists('xoac_venue_from_course', $payload)) {
            $return['venue_from_course'] = $payload['xoac_venue_from_course'];
        }
        return $return;
    }
}
