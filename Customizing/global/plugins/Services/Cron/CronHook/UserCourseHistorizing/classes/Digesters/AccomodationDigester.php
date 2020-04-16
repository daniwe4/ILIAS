<?php

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use CaT\Historization\Digester\Digester as Digester;

class AccomodationDigester implements Digester
{
    public function digest(array $payload)
    {
        $return = [];
        if (array_key_exists('xoac_venue', $payload)) {
            $return['accomodation'] = $payload['xoac_venue'];
        }
        if (array_key_exists('xoac_date_start', $payload)
            && $payload['xoac_date_start'] instanceof \DateTime) {
            $return['accomodation_date_start'] = $payload['xoac_date_start']->format('Y-m-d');
        }
        if (array_key_exists('xoac_date_end', $payload)
            && $payload['xoac_date_end'] instanceof \DateTime) {
            $return['accomodation_date_end'] = $payload['xoac_date_end']->format('Y-m-d');
        }
        if (array_key_exists('xoac_venue_from_course', $payload)) {
            $return['venue_from_course'] = (int) $payload['xoac_venue_from_course'];
        }
        return $return;
    }
}
