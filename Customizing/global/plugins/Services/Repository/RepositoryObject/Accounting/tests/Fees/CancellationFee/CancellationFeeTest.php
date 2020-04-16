<?php

declare(strict_types=1);

use CaT\Plugins\Accounting\Fees\CancellationFee\CancellationFee;
use PHPUnit\Framework\TestCase;

class CancellationFeeTest extends TestCase
{
    public function test_createDefault()
    {
        $cancellation_fee = new CancellationFee(1);

        $this->assertEquals(1, $cancellation_fee->getObjId());
        $this->assertNull($cancellation_fee->getCancellationFee());
    }

    public function test_createWithValue()
    {
        $cancellation_fee = new CancellationFee(1, 10.23);

        $this->assertEquals(1, $cancellation_fee->getObjId());
        $this->assertEquals(10.23, $cancellation_fee->getCancellationFee());
    }

    public function test_withFee()
    {
        $cancellation_fee = new CancellationFee(1);
        $cancellation_fee2 = $cancellation_fee->withCancellationFee(10.23);

        $this->assertEquals(1, $cancellation_fee->getObjId());
        $this->assertNull($cancellation_fee->getCancellationFee());

        $this->assertEquals(1, $cancellation_fee2->getObjId());
        $this->assertEquals(10.23, $cancellation_fee2->getCancellationFee());

        $this->assertNotSame($cancellation_fee, $cancellation_fee2);
    }
}
