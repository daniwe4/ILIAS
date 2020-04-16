<?php
namespace CaT\Plugins\UserBookings\Settings;

/**
 * Interface for DB handle of additional setting values
 */
interface DB
{
    /**
     * Create a new settings object for UserBookingsSettings object.
     *
     * @param int 	$obj_id
     * @param bool 	$superior_view
     *
     * @return \CaT\Plugins\UserBookings\Settings\UserBookingsSettings
     */
    public function create(
        int $obj_id,
        bool $superior_view = false,
        bool $local_evaluation = false
    ) : UserBookingsSettings;

    /**
     * Update settings of an existing repo object.
     *
     * @param	UserBookingsSettings		$settings
     */
    public function update(UserBookingsSettings $settings);

    /**
     * return UserBookingsSettings for $obj_id
     *
     * @param int $obj_id
     *
     * @return \CaT\Plugins\UserBookings\Settings\UserBookingsSettings
     */
    public function selectFor(int $obj_id);

    /**
     * Delete all information of the given obj id
     *
     * @param 	int 	$obj_id
     */
    public function deleteFor(int $obj_id);
}
