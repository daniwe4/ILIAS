<?php

declare(strict_types=1);

namespace CaT\Plugins\UserBookings\SuperiorView;

/**
 * Interface to get all booked trainings by user
 */
interface DB
{
    const SORT_BY_NAME_DESC = "sort_by_name_desc";
    const SORT_BY_NAME_ASC = "sort_by_name_asc";
    const SORT_BY_TITLE_DESC = "sort_by_title_desc";
    const SORT_BY_TITLE_ASC = "sort_by_title_asc";
    const SORT_BY_PERIOD_DESC = "sort_by_period_desc";
    const SORT_BY_PERIOD_ASC = "sort_by_period_asc";

    /**
     * Get information about trainings user has booked
     *
     * @param int[] 	$user_ids
     * @param string 	$sortation		one of the SORT_BY-consts
     *
     * @return UserBooking[]
     */
    public function getBookedTrainingsFor(array $user_ids, string $sortation, int $limit = null, int $offset = null) : array;

    /**
     * Get number of all booked traings
     *
     * @param int[]	$user_ids
     */
    public function getBookedTrainingsCountFor(array $user_ids) : int;
}
