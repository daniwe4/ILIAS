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
     * @return null
     */
    public function deleteApproveRoles($obj_id, $parent);

    /**
     * Creates a approve role object
     *
     * @param int 	$obj_id
     * @param string 	$parent
     * @param int 	$position
     * @param string 	$role_name
     *
     * @return Settings\Booking\ApproveRole
     */
    public function createApproveRole($obj_id, $parent, $position, $role_name);

    /**
     * Create new approve role assignment
     *
     * @param ApproveRole[] | null	$approve_roles
     *
     * @return null
     */
    public function createApproveRoles(array $approve_roles = null);
}
