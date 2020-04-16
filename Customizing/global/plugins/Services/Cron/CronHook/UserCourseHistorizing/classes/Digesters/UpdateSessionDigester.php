<?php

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use CaT\Historization\Digester\Digester as Digester;

class UpdateSessionDigester extends PresentSessionDigester
{
    protected function getSessionByPayload(array $payload)
    {
        return $payload['object'];
    }
    protected function getCrsIdByPayload(array $payload)
    {
        $parent_ref = (int) $payload['object']->getRefId();
        global $DIC;
        foreach ($DIC['tree']->getPathFull($parent_ref) as $node) {
            if ($node['type'] === 'crs') {
                return (int) \ilObject::_lookupObjId($node['ref_id']);
            }
        }
    }
}
