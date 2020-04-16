<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WBDManagement\Settings;

interface DB
{
    public function update(WBDManagement $settings);

    public function create(int $obj_id);

    public function selectFor(int $obj_id) : WBDManagement;

    public function deleteFor(int $obj_id);
}
