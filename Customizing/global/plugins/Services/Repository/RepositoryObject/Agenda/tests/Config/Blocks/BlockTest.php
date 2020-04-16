<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use CaT\Plugins\Agenda\Config\Blocks\Block;

class BlockTest extends TestCase
{
    public function test_create_instance()
    {
        $obj = new Block(false);
        $this->assertInstanceOf(Block::class, $obj);
    }

    public function test_properties()
    {
        $obj = new Block(false);
        $this->assertInstanceOf(Block::class, $obj);
        $this->assertFalse($obj->isEditFixedBlocks());

        $obj = new Block(true);
        $this->assertInstanceOf(Block::class, $obj);
        $this->assertTrue($obj->isEditFixedBlocks());
    }
}
