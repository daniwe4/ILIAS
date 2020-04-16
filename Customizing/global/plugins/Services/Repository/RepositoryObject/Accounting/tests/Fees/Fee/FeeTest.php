<?php

declare(strict_types=1);

use CaT\Plugins\Accounting\Fees\Fee\Fee;
use PHPUnit\Framework\TestCase;

class FeeTest extends TestCase
{
    public function setUp() : void
    {
    }

    public function test_createDefault()
    {
        $fee = new Fee(1);

        $this->assertEquals(1, $fee->getObjId());
        $this->assertNull($fee->getFee());
    }

    public function test_createWithValue()
    {
        $fee = new Fee(1, 10.23);

        $this->assertEquals(1, $fee->getObjId());
        $this->assertEquals(10.23, $fee->getFee());
    }

    public function test_withFee()
    {
        $fee = new Fee(1);
        $fee2 = $fee->withFee(10.23);

        $this->assertEquals(1, $fee->getObjId());
        $this->assertNull($fee->getFee());

        $this->assertEquals(1, $fee2->getObjId());
        $this->assertEquals(10.23, $fee2->getFee());

        $this->assertNotSame($fee, $fee2);
    }
}
