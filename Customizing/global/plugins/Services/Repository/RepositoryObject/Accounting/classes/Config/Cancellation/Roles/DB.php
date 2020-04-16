<?php

declare(strict_types=1);

namespace CaT\Plugins\Accounting\Config\Cancellation\Roles;

interface DB
{
    /**
     * @param int[] $roles
     */
    public function saveRoles(array $roles);

    /**
     * @return int[]
     */
    public function getRoles() : array;
}
