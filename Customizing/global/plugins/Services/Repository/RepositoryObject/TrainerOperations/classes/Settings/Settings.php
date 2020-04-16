<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainerOperations\Settings;

/**
 * This is the object for additional settings.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class Settings
{
    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var int[]
     */
    protected $global_roles;

    public function __construct(int $obj_id, array $global_roles)
    {
        $this->obj_id = $obj_id;
        $this->global_roles = $global_roles;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    public function getGlobalRoles() : array
    {
        return $this->global_roles;
    }

    public function withGlobalRoles(array $global_roles) : Settings
    {
        $clone = clone $this;
        $clone->global_roles = $global_roles;
        return $clone;
    }
}
