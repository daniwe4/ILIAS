<?php

namespace CaT\Plugins\UserBookings\UserBooking;

/**
 * Interface to get all booked trainings by user
 */
interface DB
{
    /**
     * Get information about trainings user has booked
     *
     * @param int 	$user_id
     *
     * @return UserBooking[]
     */
    public function getBookedTrainingsFor(int $user_id);
}
