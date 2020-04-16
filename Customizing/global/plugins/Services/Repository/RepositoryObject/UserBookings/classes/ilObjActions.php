<?php

declare(strict_types=1);

namespace CaT\Plugins\UserBookings;

/**
 * Communication class between front- and backend.
 * E.g. GUI only use this class to get information from ILIAS DB.
 */
class ilObjActions
{
    /**
     * @var \ilObjUserBookings
     */
    protected $object;

    /**
     * @var UserBooking\DB
     */
    protected $bookings_db;

    /**
     * @var Settings\DB
     */
    protected $settings_db;

    public function __construct(
        \ilObjUserBookings $object,
        UserBooking\DB $bookings_db,
        Settings\DB $settings_db,
        SuperiorView\DB $superior_view_db
    ) {
        $this->object = $object;
        $this->bookings_db = $bookings_db;
        $this->settings_db = $settings_db;
        $this->superior_view_db = $superior_view_db;
    }

    /**
     * Get instance of current object
     *
     * @throws Exception 	if no object is set
     * @return \ilObjUserBookings
     */
    public function getObject()
    {
        if ($this->object === null) {
            throw new \Exception("No object was set");
        }

        return $this->object;
    }

    /**
     * Get booked trainings of user
     *
     * @param int 	$user_id
     *
     * @return UserBooking[]
     */
    public function getBookedTrainingOf($user_id)
    {
        assert('is_int($user_id)');
        return $this->bookings_db->getBookedTrainingsFor($user_id);
    }

    /**
     * Get booked trainings for pool of user
     *
     * @param int[] 	$user_ids
     * @param string 	$sortation 	one of the SORT_BY-consts
     *
     * @return UserBooking[]
     */
    public function getBookedTrainingsFor(array $user_ids, string $sortation, int $limit = null, int $offset = null) : array
    {
        return $this->superior_view_db->getBookedTrainingsFor($user_ids, $sortation, $limit, $offset);
    }

    /**
     * Get number of all booked traings
     *
     * @param int[]	$user_ids
     */
    public function getBookedTrainingsCountFor(array $user_ids) : int
    {
        return $this->superior_view_db->getBookedTrainingsCountFor($user_ids);
    }

    /**
     * Creates an empty settings object
     *
     * @return void
     */
    public function createEmpty()
    {
        return $this->settings_db->create((int) $this->getObject()->getId());
    }

    /**
     * Updates the settings
     *
     * @param Settings\UserBookingsSettings 	$settings
     *
     * @return void
     */
    public function update(Settings\UserBookingsSettings $settings)
    {
        $this->settings_db->update($settings);
    }

    /**
     * Selects settngs for object
     *
     * @return Settings\UserBookingsSettings
     */
    public function select()
    {
        return $this->settings_db->selectFor((int) $this->getObject()->getId());
    }

    /**
     * Delete settings for object
     *
     * @return void
     */
    public function delete()
    {
        $this->settings_db->deleteFor((int) $this->getObject()->getId());
    }

    public function isReccomendationAllowed() : bool
    {
        $settings = $this->select();
        return $settings->getRecommendationAllowed();
    }
}
