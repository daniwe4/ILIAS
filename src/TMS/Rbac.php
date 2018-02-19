<?php

declare(strict_types=1);

namespace ILIAS\TMS;

interface Rbac
{
    /**
     * Get all assigned roles for user
     * @return int[]
     */
    public function getAssignedRolesOf(int $user_id) : array;

    /**
     * Get all assigned global roles for user
     * @return int[]
     */
    public function getAssignedGlobalRolesOf(int $user_id) : array;
}
