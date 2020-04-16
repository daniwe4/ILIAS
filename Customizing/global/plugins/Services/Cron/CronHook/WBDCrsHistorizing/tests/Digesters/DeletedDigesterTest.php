<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WBDCrsHistorizing\Digesters;

use PHPUnit\Framework\TestCase;

class DeletedDigesterTest extends TestCase
{
    public function testDigest() : void
    {
        $obj = new DeletedDigester();

        $result = $obj->digest([]);

        $this->assertTrue($result['deleted']);
    }
}
