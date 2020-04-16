<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use PHPUnit\Framework\TestCase;

class TitleDigesterTest extends TestCase
{
    /**
     * @var TitleDigester
     */
    protected $obj;

    public function setUp() : void
    {
        $this->obj = new TitleDigester();
    }

    public function testDigestWithoutPayload() : void
    {
        $result = $this->obj->digest([]);

        $this->assertEquals(0, count($result));
    }

    public function testDigestWithPayload() : void
    {
        $mocks = new Mocks();

        $crs = $mocks->getCrsMock();
        $crs
            ->expects($this->once())
            ->method('getTitle')
            ->willReturn('test_title')
        ;

        $payload = [
            'object' => $crs
        ];

        $result = $this->obj->digest($payload);

        $this->assertEquals('test_title', $result['title']);
    }
}
