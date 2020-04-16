<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use PHPUnit\Framework\TestCase;
use CaT\Plugins\Accounting;

require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/Accounting/classes/class.ilObjAccounting.php";

class AccountingModifiedDigesterTest extends TestCase
{
    /**
     * @var Mocks
     */
    protected $mocks;

    public function setUp() : void
    {
        $this->mocks = new Mocks();
    }

    public function testDigest() : void
    {
        $crs = $this->mocks->getCrsMock();

        $settings = $this->createMock(Accounting\Settings\Settings::class);
        $settings
            ->expects($this->once())
            ->method('getFinalized')
            ->willReturn(true)
        ;

        $fee = $this->createMock(Accounting\Fees\Fee\Fee::class);
        $fee
            ->expects($this->once())
            ->method('getFee')
            ->willReturn(30)
        ;

        $fee_actions = $this->createMock(Accounting\Fees\Fee\ilActions::class);
        $fee_actions
            ->expects($this->once())
            ->method('select')
            ->willReturn($fee)
        ;

        $cancelation_fee = $this->createMock(Accounting\Fees\CancellationFee\CancellationFee::class);
        $cancelation_fee
            ->expects($this->once())
            ->method('getCancellationFee')
            ->willReturn(40)
        ;

        $cancelation_fee_actions = $this->createMock(Accounting\Fees\CancellationFee\ilActions::class);
        $cancelation_fee_actions
            ->expects($this->once())
            ->method('select')
            ->willReturn($cancelation_fee)
        ;

        $actions = $this->createMock(Accounting\ilObjectActions::class);
        $actions
            ->expects($this->once())
            ->method('getNetSum')
            ->willReturn(10.0)
        ;
        $actions
            ->expects($this->once())
            ->method('getGrossSum')
            ->willReturn(20.0)
        ;
        $actions
            ->expects($this->once())
            ->method('getFeeActions')
            ->willReturn($fee_actions)
        ;
        $actions
            ->expects($this->once())
            ->method('getCancellationFeeActions')
            ->willReturn($cancelation_fee_actions)
        ;

        $acc = $this->createMock('ilObjAccounting');
        $acc
            ->expects($this->once())
            ->method('getParentCourse')
            ->willReturn($crs)
        ;
        $acc
            ->expects($this->once())
            ->method('getObjectActions')
            ->willReturn($actions)
        ;
        $acc
            ->expects($this->once())
            ->method('getSettings')
            ->willReturn($settings)
        ;

        $payload = ['xacc' => $acc];
        $obj = new AccountingModifiedDigester();
        $result = $obj->digest($payload);

        $this->assertEquals('22', $result['crs_id']);
        $this->assertEquals(10.0, $result['net_total_cost']);
        $this->assertEquals(20.0, $result['gross_total_cost']);
        $this->assertTrue($result['costcenter_finalized']);
        $this->assertEquals(30, $result['fee']);
        $this->assertEquals(40, $result['max_cancellation_fee']);
    }
}
