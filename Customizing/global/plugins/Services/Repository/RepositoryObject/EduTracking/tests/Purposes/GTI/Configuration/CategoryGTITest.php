<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\EduTracking\Purposes\GTI\Configuration;

use PHPUnit\Framework\TestCase;

class CategoryGTITest extends TestCase
{
    public function testCreate() : void
    {
        $obj = new CategoryGTI(22, 'test');

        $this->assertEquals(22, $obj->getId());
        $this->assertEquals('test', $obj->getName());
    }
}
