<?php
namespace CaT\Plugins\BookingModalities\Overview;

/**
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
interface DB
{
    /**
     * Get all bookings without cancellations for course id.
     *
     * @param string $order_field
     * @param string $order_direction
     * @param  int $crs_id Object id.
     * @return Overview[]
     */
    public function getBookings($crs_id, $order_field, $order_direction, $limit, $offset, $selected_columns);

    /**
     * Get the amount of all bookings per course id.
     *
     * @param  int $crs_id
     * @return int
     */
    public function getMaxBookings($crs_id);

    /**
     * Get all cancelled bookings.
     *
     * @param string $order_field
     * @param string $order_direction
     * @param  int $crs_id Object id.
     * @return Overview[]
     */
    public function getCancellations($crs_id, $order_field, $order_direction, $limit, $offset, $selected_columns);

    /**
     * Get the amount of all cancellations per course id.
     *
     * @param  int $crs_id
     * @return int
     */
    public function getMaxCancellations($crs_id);
}
