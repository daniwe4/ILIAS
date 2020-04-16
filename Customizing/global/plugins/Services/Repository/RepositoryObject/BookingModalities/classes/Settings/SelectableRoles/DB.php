<?php

namespace CaT\Plugins\BookingModalities\Settings\SelectableRoles;

/**
 * Describes the db handler for selectable roles
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface DB
{
    /**
     * Unassign all roles
     *
     * @return null
     */
    public function unassignRoles();

    /**
     * Assign a role
     *
     * @param int[] 	$roles
     *
     * @return null
     */
    public function assignRoles(array $roles);

    /**
     * Get assigned roles
     *
     * @return string[] | []
     */
    public function select();
}
