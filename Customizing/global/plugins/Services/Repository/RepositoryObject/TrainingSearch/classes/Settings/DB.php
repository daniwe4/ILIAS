<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\TrainingSearch\Settings;

interface DB
{
    public function create(int $obj_id) : Settings;
    public function select(int $obj_id) : Settings;
    public function update(Settings $settings);
    public function delete(int $obj_id);
}
