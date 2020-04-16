<?php

namespace CaT\Plugins\EduTracking\Purposes\WBD;

class IliasWBDObjectProvider implements WBDObjectProvider
{
    public function __construct(\ilTree $tree)
    {
        $this->tree = $tree;
    }

    public function getFirstChildOfByType($ref_id, $search_type)
    {
        $children = $this->tree->getChilds($ref_id);
        while ($child = array_shift($children)) {
            if ($child['type'] === $search_type) {
                return \ilObjectFactory::getInstanceByRefId($child['child']);
            }
            foreach ($this->tree->getChilds($child['child']) as $sub) {
                array_push($children, $sub);
            }
        }
        return null;
    }
}
