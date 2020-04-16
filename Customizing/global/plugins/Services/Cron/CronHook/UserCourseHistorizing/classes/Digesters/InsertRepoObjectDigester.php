<?php declare(strict_types = 1);

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use CaT\Historization\Digester\Digester as Digester;

class InsertRepoObjectDigester implements Digester
{
    use RepositoryPluginLocatorTrait;
    use RepositoryObjectLocatorTrait;

    public function digest(array $payload)
    {
        return $this->getInsertedPayloadForCourseAbove(
            (int) $payload['parent_id'],
            (int) $payload['node_id'],
            $this->loadPluginForType($this->lookupTypeByRefId((int) $payload['node_id']))
        );
    }


    public function getInsertedPayloadForCourseAbove(
        int $parent_ref_id,
        int $inserted_ref_id,
        \HistorizedRepositoryPlugin $hrp
    ) : array {
        $crs_ref_id = $this->locateCrsIdInTreeAboveNode($parent_ref_id, $hrp);
        $crs_obj_id = $this->objIdByRefId($crs_ref_id);
        $payload = ['crs_id' => $crs_obj_id];
        $obj = \ilObjectFactory::getInstanceByRefId($inserted_ref_id);
        return array_merge($payload, $hrp->extractPayloadByPluginObject($obj));
    }
}
