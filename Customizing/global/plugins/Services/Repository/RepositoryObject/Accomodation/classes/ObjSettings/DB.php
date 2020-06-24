<?php

declare(strict_types=1);

namespace CaT\Plugins\Accomodation\ObjSettings;

use DateTime;

/**
 * Interface for DB handle of settings
 *
 * @author 	Nils Haagen 	<nils.haagen@concepts-and-training.de>
 */
interface DB
{
    public function create(
        int $obj_id,
        bool $dates_from_course = true,
        DateTime $start_date = null,
        DateTime $end_date = null,
        int $location_obj_id = null,
        bool $location_from_course = false,
        bool $allow_prior_day = null,
        bool $allow_following_day = null,
        int $booking_end = null,
        bool $mailing_use_venue_settings = true,
        string $mail_recipient = "",
        int $send_days_before = 0,
        int $send_reminder_days_before = 0,
        bool $edit_notes = false
    ) : ObjSettings;

    /**
     * Update settings of an existing repo object.
     *
     * @param	ObjSettings		$settings
     */
    public function update(ObjSettings $settings);

    /**
     * return ObjSettings for $obj_id
     *
     * @param int $obj_id
     *
     * @return ObjSettings
     */
    public function selectFor(int $obj_id) : ObjSettings;

    /**
     * Delete all information of the given obj id
     *
     * @param 	int 	$obj_id
     */
    public function deleteFor(int $obj_id);
}
