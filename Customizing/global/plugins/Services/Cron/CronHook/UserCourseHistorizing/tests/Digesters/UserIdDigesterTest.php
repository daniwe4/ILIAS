<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use PHPUnit\Framework\TestCase;

class UserIdDigesterTest extends TestCase
{
    /**
     * @var UserIdDigester
     */
    protected $obj;

    public function setUp() : void
    {
        $this->obj = new UserIdDigester();
    }

    public function testDigestWithoutPayload() : void
    {
        $result = $this->obj->digest([]);

        $this->assertEquals(0, count($result));
    }

    public function testDigestWithUserId() : void
    {
        $payload = [
            'usr_id' => 20
        ];

        $result = $this->obj->digest($payload);

        $this->assertEquals(0, count($result));
    }

    public function testDigestWithXoacUserId() : void
    {
        $payload = [
            'xoac_usr_id' => 20
        ];

        $result = $this->obj->digest($payload);

        $this->assertEquals(20, $result['usr_id']);
    }
}
