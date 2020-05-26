<?php

namespace ILIAS\TMS\CourseCreation;

trait ChildAssistant
{
    /**
     * Get all children by type recursive
     *
     * @param int 	$ref_id
     * @param string 	$search_type
     *
     * @return Object 	of search type
     */
    protected function getAllChildrenOfByType(int $ref_id, string $search_type) : array
    {
        $tree = $this->getDIC()["tree"];
        $children = $tree->getSubTree(
            $tree->getNodeData($ref_id),
            true,
            $search_type
        );

        return array_map(
            function ($node) {
                return \ilObjectFactory::getInstanceByRefId($node["child"]);
            },
            $children
        );
    }

    /**
     * Checks the access to object for current user
     *
     * @param string[] 	$permissions
     * @param int 	$ref_id
     *
     * @return bool
     */
    protected function checkAccess(array $permissions, $ref_id)
    {
        $access = $this->getDIC()->access();
        foreach ($permissions as $permission) {
            if (!$access->checkAccessOfUser($this->user_id, $permission, "", $ref_id)) {
                return false;
            }
        }

        return true;
    }
}
