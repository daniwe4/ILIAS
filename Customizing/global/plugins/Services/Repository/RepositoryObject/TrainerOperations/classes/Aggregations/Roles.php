<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainerOperations\Aggregations;

/**
 * Collect roles and positions.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class Roles
{
    /**
     * @var \ilRbacReview
     */
    protected $rbac_review;

    public function __construct(
        \ilRbacReview $rbacreview
    ) {
        $this->rbac_review = $rbacreview;
    }

    protected function getTitleForObjectId(int $obj_id) : string
    {
        return \ilObject::_lookupTitle($obj_id);
    }

    public function getGlobalRoles() : array
    {
        $rbac_roles = $this->rbac_review->getGlobalRoles();
        $roles = [];
        foreach ($rbac_roles as $role_id) {
            $role_id = (int) $role_id;
            $roles[$role_id] = $this->getTitleForObjectId($role_id);
        }
        return $roles;
    }
}
