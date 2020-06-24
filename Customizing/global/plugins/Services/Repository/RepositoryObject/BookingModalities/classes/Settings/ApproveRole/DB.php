<?php

namespace CaT\Plugins\BookingModalities\Settings\ApproveRole;

/**
 * Inteface for db handling of approve role
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface DB
{
    /**
     * Delete all approve roles
     *
     * @param int   $obj_id
     * @param string 	$parent
     *
     * @return void
     */
    public function deleteApproveRoles(int $obj_id, string $parent) : void;

    /**
     * Creates a approve role object
     *
     * @param int 	$obj_id
     * @param string $parent
     * @param int 	$position
     * @param int 	$role_id
     *
     * @return ApproveRole
     */
    public function createApproveRole(int $obj_id, string $parent, int $position, int $role_name) : ApproveRole;

    /**
     * Create new approve role assignment
     *
     * @param ApproveRole[] | null	$approve_roles
     *
     * @return void
     */
    public function createApproveRoles(array $approve_roles = null) : void;
}
