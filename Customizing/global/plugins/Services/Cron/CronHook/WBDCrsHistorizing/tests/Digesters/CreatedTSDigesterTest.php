<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WBDCrsHistorizing\Digesters;

use PHPUnit\Framework\TestCase;

class CreatedTSDigesterTest extends TestCase
{
    public function testDigest() : void
    {
        $obj = new CreatedTSDigester();

        $before = time();
        $result = $obj->digest([]);
        $after = time();

        $this->assertTrue($result['created_ts'] >= $before);
        $this->assertTrue($result['created_ts'] <= $after);
    }
}
