<?php

/* Copyright (c) 2018 Daniel Weise <daniel.weise@concepts-and-training.de> */
/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\Accounting\Settings;

/**
 * Interface for DB handle of additional setting values
 */
interface DB
{
    public function update(Settings $settings);
    public function selectFor(int $obj_id) : Settings;
    public function deleteFor(int $obj_id);
    public function insert(int $obj_id, bool $finalized, bool $edit_fee);
}
