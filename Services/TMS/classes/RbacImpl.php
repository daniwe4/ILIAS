<?php

declare(strict_types=1);

use ILIAS\TMS\Rbac;

class RbacImpl implements Rbac
{
    /**
     * @var ilRbacReview
     */
    protected $rbacreview;

    public function __construct(ilRbacReview $rbacreview)
    {
        $this->rbacreview = $rbacreview;
    }
    /**
     * @inheritdoc
     */
    public function getAssignedRolesOf(int $user_id) : array
    {
        return array_map(
            function ($role_id) {
                return (int) $role_id;
            },
            $this->rbacreview->assignedRoles($user_id)
        );
    }

    /**
     * @inheritdoc
     */
    public function getAssignedGlobalRolesOf(int $user_id) : array
    {
        return array_map(
            function ($role_id) {
                return (int) $role_id;
            },
            $this->rbacreview->assignedGlobalRoles($user_id)
        );
    }
}
