<?php declare(strict_types = 1);

namespace CaT\Plugins\WBDCrsHistorizing\Digesters;

trait RepositoryObjectLocatorTrait
{
    protected function locateNodeInTreeUnderNode(
        int $ref_id,
        \HistorizedRepositoryPlugin $hrp
    ) {
        $tree = $hrp->getTree();
        $sub_ref_id = array_shift(
            $tree->getSubTree(
                $tree->getNodeData($ref_id),
                false,
                $hrp->getObjType()
            )
        );
        if ($sub_ref_id) {
            return $sub_ref_id;
        }
        return null;
    }

    protected function locateCrsIdInTreeAboveNode(
        int $ref_id,
        \HistorizedRepositoryPlugin $hrp
    ) {
        $tree = $hrp->getTree();
        foreach ($tree->getPathFull($ref_id) as $hop) {
            if ($hop['type'] === 'crs') {
                return (int) $hop["ref_id"];
            }
        }
        return null;
    }

    protected function objIdByRefId(int $ref_id) : int
    {
        return \ilObject::_lookupObjId($ref_id);
    }
}
