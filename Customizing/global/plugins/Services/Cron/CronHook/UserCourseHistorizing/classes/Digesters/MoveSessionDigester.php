<?php

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use CaT\Historization\Digester\Digester as Digester;

class MoveSessionDigester extends RemoveSessionDigester
{
    protected function getSesionIdByPalyoad(array $payload)
    {
        return (int) \ilObject::_lookupObjId($payload['node_id']);
    }

    protected function getCrsIdByPayload(array $payload)
    {
        $parent_ref = (int) $payload['old_parent_id'];
        global $DIC;
        foreach ($DIC['tree']->getPathFull($parent_ref) as $node) {
            if ($node['type'] === 'crs') {
                return (int) \ilObject::_lookupObjId($node['ref_id']);
            }
        }
    }
}
