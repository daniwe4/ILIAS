<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use PHPUnit\Framework\TestCase;

class UserCancellationFeeDigesterTest extends TestCase
{
    /**
     * @var UserCancellationFeeDigester
     */
    protected $obj;

    public function setUp() : void
    {
        $this->obj = new UserCancellationFeeDigester();
    }

    public function testDigestWithoutPayload() : void
    {
        $result = $this->obj->digest([]);

        $this->assertEquals(0, count($result));
    }

    public function testDigestWithPayload() : void
    {
        $payload = [
            'cancellationfee' => 20
        ];

        $result = $this->obj->digest($payload);

        $this->assertEquals(20, $result['cancellation_fee']);
    }
}
