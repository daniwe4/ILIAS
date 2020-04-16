<?php


namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use CaT\Historization\Digester\Digester as Digester;

class ProviderDigester implements Digester
{
    public function digest(array $payload)
    {
        $return = [];
        if (\ilPluginAdmin::isPluginActive('trainingprovider')) {
            if ($payload['object']) {
                $obj_id = (int) $payload['object']->getId();
            } elseif ($payload['crs_id']) {
                $obj_id = (int) $payload['crs_id'];
            }
            try {
                $plugin = \ilPluginAdmin::getPluginObjectById('trainingprovider');
                list($provider_id, $provider, $custom_assignment) = $plugin->getProviderInfos($obj_id);
            } catch (\Exception $e) {
                $provider = '';
                $custom_assignment = false;
            }
        }

        $return['provider'] = $provider;
        $return['provider_freetext'] = $custom_assignment;

        return $return;
    }
}
