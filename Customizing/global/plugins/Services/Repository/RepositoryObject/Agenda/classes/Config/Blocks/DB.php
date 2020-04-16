<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\Agenda\Config\Blocks;

interface DB
{
    public function selectBlockConfig() : Block;
    public function saveBlockConfig(bool $edit_in_course_creation);
}
