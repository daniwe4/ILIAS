<?php

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use CaT\Historization\Digester\Digester as Digester;

class VenueDigester implements Digester
{
    public function digest(array $payload)
    {
        $return = [];
        if (\ilPluginAdmin::isPluginActive('venues')) {
            if ($payload['object']) {
                $obj_id = (int) $payload['object']->getId();
            } elseif ($payload['crs_id']) {
                $obj_id = (int) $payload['crs_id'];
            }

            try {
                $plugin = \ilPluginAdmin::getPluginObjectById('venues');
                list($venue_id, $city, $address, $name, $postcode, $custom_assignment) = $plugin->getVenueInfos($obj_id);
            } catch (\Exception $e) {
                $name = '';
                $custom_assignment = false;
            }

            $return['venue'] = $name;
            $return['venue_freetext'] = $custom_assignment;
        }
        return $return;
    }
}
