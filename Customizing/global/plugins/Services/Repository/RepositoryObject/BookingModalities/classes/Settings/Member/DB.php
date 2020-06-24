<?php

namespace CaT\Plugins\BookingModalities\Settings\Member;

/**
 * Interface for DB handle of additional setting values
 */
interface DB
{
    /**
     * Create a new member settings.
     *
     * @param int 		$obj_id
     *
     * @return Member
     */
    public function create(int $obj_id);

    /**
     * Update member settings.
     *
     * @param Member	$member_settings
     */
    public function update(Member $member_settings);

    /**
     * Get member settings for obj_id
     *
     * @param int $obj_id
     *
     * @return Member
     */
    public function selectFor(int $obj_id);

    /**
     * Delete all settings of the given obj id
     *
     * @param int 	$obj_id
     */
    public function deleteFor(int $obj_id);
}
