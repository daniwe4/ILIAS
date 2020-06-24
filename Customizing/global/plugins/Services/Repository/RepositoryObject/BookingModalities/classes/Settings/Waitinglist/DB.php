<?php

namespace CaT\Plugins\BookingModalities\Settings\Waitinglist;

/**
 * Interface for DB handle of additional setting values
 */
interface DB
{
    /**
     * Create a new waitinglist settings.
     *
     * @param int 		$obj_id
     *
     * @return Waitinglist
     */
    public function create(int $obj_id);

    /**
     * Update waitinglist settings.
     *
     * @param Waitinglist	$waitinglist_settings
     */
    public function update(Waitinglist $waitinglist_settings);

    /**
     * Get waitinglist settings for obj_id
     *
     * @param int $obj_id
     *
     * @return Waitinglist
     */
    public function selectFor(int $obj_id);

    /**
     * Delete all settings of the given obj id
     *
     * @param int 	$obj_id
     */
    public function deleteFor(int $obj_id);
}
