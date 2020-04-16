<?php declare(strict_types = 1);

namespace CaT\Plugins\WBDCrsHistorizing\Digesters;

use CaT\Historization\Digester\Digester as Digester;

class DeleteRepoObjectDigester implements Digester
{
    use RepositoryPluginLocatorTrait;
    use RepositoryObjectLocatorTrait;

    public function digest(array $payload)
    {
        return $this->getDeletedPayloadForCourseAbove(
            (int) $payload['old_parent_ref_id'],
            $this->loadPluginForType($payload['type'])
        );
    }


    public function getDeletedPayloadForCourseAbove(
        int $deleted_parent_ref_id,
        \HistorizedRepositoryPlugin $hrp
    ) : array {
        $crs_ref_id = $this->locateCrsIdInTreeAboveNode($deleted_parent_ref_id, $hrp);
        $crs_obj_id = $this->objIdByRefId($crs_ref_id);
        $sub_ref_id = $this->locateNodeInTreeUnderNode($crs_ref_id, $hrp);
        $payload = ['crs_id' => $crs_obj_id];
        if (!$sub_ref_id) {
            return array_merge($payload, $hrp->getEmptyPayload());
        } else {
            $obj = \ilObjectFactory::getInstanceByRefId($sub_ref_id);
            return array_merge($payload, $hrp->extractPayloadByPluginObject($obj));
        }
    }
}
