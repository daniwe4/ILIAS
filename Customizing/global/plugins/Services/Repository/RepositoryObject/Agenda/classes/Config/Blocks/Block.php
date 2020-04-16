<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\Agenda\Config\Blocks;

class Block
{
    /**
     * @var bool
     */
    protected $edit_fixed_blocks;

    public function __construct(bool $edit_fixed_blocks)
    {
        $this->edit_fixed_blocks = $edit_fixed_blocks;
    }

    public function isEditFixedBlocks() : bool
    {
        return $this->edit_fixed_blocks;
    }
}
