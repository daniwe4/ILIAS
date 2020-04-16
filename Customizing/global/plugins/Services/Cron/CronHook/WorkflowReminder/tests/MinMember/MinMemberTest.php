<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WorkflowReminder\MinMember;

use PHPUnit\Framework\TestCase;

class MinMemberTest extends TestCase
{
    public function test_create_instance()
    {
        $crs_ref_id = 10;
        $date = new \DateTime();
        $child_ref_id = 12;
        $min_member = 5;

        $data = new MinMember(
            $crs_ref_id,
            $date,
            $child_ref_id,
            $min_member
        );

        $this->assertInstanceOf(MinMember::class, $data);
        $this->assertEquals($crs_ref_id, $data->getCrsRefId());
        $this->assertEquals($date, $data->getBeginDate());
        $this->assertSame($date, $data->getBeginDate());
        $this->assertEquals($child_ref_id, $data->getChildRefId());
        $this->assertEquals($min_member, $data->getMinMember());
    }
}
