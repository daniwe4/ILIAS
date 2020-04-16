<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WBDCrsHistorizing\Digesters;

use PHPUnit\Framework\TestCase;

class CourseIdDigesterTest extends TestCase
{
    public function testDigest() : void
    {
        $obj = new CourseIdDigester();
        $mock = new Mocks();
        $crs = $mock->getCrsMock();

        $payload = [
            'object' => $crs,
            'obj_id' => 33,
            'xoac_parent_crs_info' => [
                'obj_id' => 44
            ],
            'crs_obj_id' => 55
        ];

        $expected = 22;
        foreach ($payload as $key => $value) {
            $result = $obj->digest([$key => $value]);
            $this->assertEquals($expected, $result['crs_id']);
            $expected += 11;
        }
    }
}
