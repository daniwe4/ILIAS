<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

namespace CaT\Plugins\Agenda\Settings;

use DateTime;

interface DB
{
    /**
     * Update settings of an existing repo object.
     */
    public function update(Settings $settings);

    /**
     * Create a new settings object for Settings object.
     */
    public function create(int $obj_id) : Settings;

    /**
     * return Settings for $obj_id
     */
    public function selectFor(int $obj_id) : Settings;

    /**
     * Delete all information of the given obj id
     */
    public function deleteFor(int $obj_id);
}
