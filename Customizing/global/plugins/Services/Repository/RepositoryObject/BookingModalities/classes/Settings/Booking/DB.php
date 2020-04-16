<?php

namespace CaT\Plugins\BookingModalities\Settings\Booking;

/**
 * Interface for DB handle of additional setting values
 */
interface DB
{
    /**
     * Create a new booking settings.
     *
     * @param int 		$obj_id
     *
     * @return Booking
     */
    public function create($obj_id);

    /**
     * Update booking settings.
     *
     * @param Booking	$booking_settings
     */
    public function update(Booking $booking_settings);

    /**
     * Get booking settings for obj_id
     *
     * @param int $obj_id
     *
     * @return Booking
     */
    public function selectFor($obj_id);

    /**
     * Delete all settings of the given obj id
     *
     * @param int 	$obj_id
     */
    public function deleteFor($obj_id);
}
