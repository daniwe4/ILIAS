<?php

declare(strict_types=1);

namespace CaT\Plugins\BookingModalities\Settings\ApproveRole;

/**
 * Keeps information of role there must approve an the postion
 *
 * @author 	Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ApproveRole
{
    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var string
     */
    protected $parent;

    /**
     * @var int
     */
    protected $position;

    /**
     * @var int
     */
    protected $role_id;

    public function __construct(int $obj_id, string $parent, int $position, int $role_id)
    {
        $this->obj_id = $obj_id;
        $this->parent = $parent;
        $this->position = $position;
        $this->role_id = $role_id;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    public function getParent() : string
    {
        return $this->parent;
    }

    public function getPosition() : int
    {
        return $this->position;
    }

    public function getRoleId() : int
    {
        return $this->role_id;
    }
}
