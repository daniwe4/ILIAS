<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use PHPUnit\Framework\TestCase;

class CreatedTSDigesterTest extends TestCase
{
    public function testDigest() : void
    {
        $obj = new CreatedTSDigester();

        $before = time();
        $result = $obj->digest([]);
        $after = time();

        if ($result['created_ts'] >= $before && $result['created_ts'] <= $after) {
            $this->assertTrue(true);
            return;
        }

        $this->assertTrue(false);
    }
}
