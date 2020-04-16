<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\ScaledFeedback\Settings;

interface DB
{
    /**
     * Create a new entry for any settings and returns the new object
     */
    public function create(int $obj_id) : Settings;

    /**
     * Update any settings
     */
    public function update(Settings $settings);

    /**
     * Select any defined settings
     *
     * @return Settings[]
     */
    public function selectAll() : array;

    /**
     * Delete a settings by id
     */
    public function delete(int $id);
}
