<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use PHPUnit\Framework\TestCase;

class DeletedDigesterTest extends TestCase
{
    public function testDigestWithDeletedTrue() : void
    {
        $obj = new DeletedDigester(true);
        $result = $obj->digest([]);
        $this->assertTrue($result['deleted']);
    }

    public function testDigestWithDeletedFalse() : void
    {
        $obj = new DeletedDigester(false);
        $result = $obj->digest([]);
        $this->assertFalse($result['deleted']);
    }
}
