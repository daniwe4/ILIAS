<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see ./LICENSE */

namespace CaT\Plugins\Accomodation;

/**
 * Interface for the plugin object.
 * Accomodations bind a user to a venue at certain dates:
 * users can book overnight-stays for a course.
 */
interface ObjAccomodation
{

    /**
     * Get actions for this object
     *
     * @return \CaT\Plugins\Accounting\ilActions
     */
    public function getActions();

    /**
     * @return ObjSettings\DB
     */
    public function getObjSettingsDB();

    /**
     * @return Reservation\DB
     */
    public function getReservationDB();

    /**
     * @return Venue\DB
     */
    public function getVenueDB();

    /**
     * @return ObjSettings\ObjSettings
     */
    public function getObjSettings();

    /**
     * @param \Closure $update_function
     * @return void
     */
    public function updateObjSettings(\Closure $update_function);

    /**
     * get the venue as configured in settings
     * @return Venue | null
     */
    public function getVenue();
}
