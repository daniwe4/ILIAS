<?php

namespace CaT\Plugins\BookingModalities\Settings\Storno;

/**
 * Interface for DB handle of additional setting values
 */
interface DB
{
    /**
     * Create a new storno settings.
     *
     * @param int 		$obj_id
     *
     * @return Storno
     */
    public function create(int $obj_id);

    /**
     * Update storno settings.
     *
     * @param Storno	$storno_settings
     */
    public function update(Storno $storno_settings);

    /**
     * Get storno settings for obj_id
     *
     * @param int $obj_id
     *
     * @return Storno
     */
    public function selectFor(int $obj_id);

    /**
     * Delete all settings of the given obj id
     *
     * @param int 	$obj_id
     */
    public function deleteFor(int $obj_id);
}
