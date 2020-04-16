<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use PHPUnit\Framework\TestCase;

require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/Accounting/classes/class.ilObjAccounting.php";

class AccountingDeletedDigesterTest extends TestCase
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

        $acc = $this->createMock('ilObjAccounting');
        $acc
            ->expects($this->once())
            ->method('getParentCourse')
            ->willReturn($crs)
        ;

        $payload = ['xacc' => $acc];
        $obj = new AccountingDeletedDigester();
        $result = $obj->digest($payload);

        $this->assertEquals('22', $result['crs_id']);
        $this->assertIsFloat($result['net_total_cost']);
        $this->assertIsFloat($result['gross_total_cost']);
        $this->assertEquals(0.0, $result['net_total_cost']);
        $this->assertEquals(0.0, $result['gross_total_cost']);
        $this->assertNull($result['costcenter_finalized']);
    }
}
