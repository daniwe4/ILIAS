<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use PHPUnit\Framework\TestCase;

class CourseIdDigesterTest extends TestCase
{
    /**
     * @var Mocks
     */
    protected $mocks;

    /**
     * @var CourseIdDigester
     */
    protected $obj;

    public function setUp() : void
    {
        $this->mocks = new Mocks();
        $this->obj = new CourseIdDigester();
    }

    public function testDigestWithCourseObject() : void
    {
        $payload = [
            'object' => $this->mocks->getCrsMock()
        ];

        $result = $this->obj->digest($payload);

        $this->assertEquals(22, $result['crs_id']);
    }

    public function testDigestWithObjId() : void
    {
        $payload = [
            'obj_id' => 33
        ];


        $result = $this->obj->digest($payload);

        $this->assertEquals(33, $result['crs_id']);
    }

    public function testDigestWithXoacParentCrsInfo() : void
    {
        $payload = [
            'xoac_parent_crs_info' => [
                'obj_id' => 44
            ]
        ];


        $result = $this->obj->digest($payload);

        $this->assertEquals(44, $result['crs_id']);
    }

    public function testDigestWithCrsObjId() : void
    {
        $payload = [
            'crs_obj_id' => 44
        ];


        $result = $this->obj->digest($payload);

        $this->assertEquals(44, $result['crs_id']);
    }
}
