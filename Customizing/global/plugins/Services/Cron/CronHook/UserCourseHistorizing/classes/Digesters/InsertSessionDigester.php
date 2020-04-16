<?php

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use CaT\Historization\Digester\Digester as Digester;

class InsertSessionDigester extends PresentSessionDigester
{
    protected function getSessionByPayload(array $payload)
    {
        return \ilObjectFactory::getInstanceByRefId($payload['node_id']);
    }
    protected function getCrsIdByPayload(array $payload)
    {
        $parent_ref = (int) $payload['parent_id'];
        global $DIC;
        foreach ($DIC['tree']->getPathFull($parent_ref) as $node) {
            if ($node['type'] === 'crs') {
                return (int) \ilObject::_lookupObjId($node['ref_id']);
            }
        }
    }
}
