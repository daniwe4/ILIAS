<?php

namespace CaT\Plugins\BookingModalities\Settings\DownloadableDocument;

/**
 * Interface for DB handle of role-specific documents
 */
interface DB
{
    /**
     * Create a new setting.
     *
     * @param int 		$role_id
     *
     * @return Relevance
     */
    public function createRoleSetting(int $role_id);

    /**
     * Get setting for a role.
     *
     * @param int 		$role_id
     *
     * @return Relevance | null
     */
    public function selectRoleSetting(int $role_id);

    /**
     * Update setting.
     *
     * @param Relevance	$relevance
     * @return void
     */
    public function updateRoleSetting(Relevance $relevance);

    /**
     * Get settings for all roles
     *
     * @return Relevance[]
     */
    public function select();

    /**
     * Delete assignment for the given role
     *
     * @param int 	$role_id
     */
    public function deleteFor(int $role_id);
}
