<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WorkflowReminder\NotFinalized\CourseMember;

use PHPUnit\Framework\TestCase;

class NotFinalizedTest extends TestCase
{
    public function test_create_instance()
    {
        $crs_ref_id = 10;
        $child_ref_id = 12;

        $data = new NotFinalized(
            $crs_ref_id,
            $child_ref_id
        );

        $this->assertInstanceOf(NotFinalized::class, $data);
        $this->assertEquals($crs_ref_id, $data->getCrsRefId());
        $this->assertEquals($child_ref_id, $data->getChildRefId());
    }
}
