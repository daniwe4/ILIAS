<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\AutomaticCancelWaitinglist;

use PHPUnit\Framework\TestCase;

class CourseDataTest extends TestCase
{
    public function test_create_instance()
    {
        $crs_ref_id = 10;
        $date = new \DateTime();
        $xbkm_ref_id = 12;
        $cancellation = 5;

        $crs_data = new Database\CourseData(
            $crs_ref_id,
            $date,
            $xbkm_ref_id,
            $cancellation
        );

        $this->assertInstanceOf(Database\CourseData::class, $crs_data);
        $this->assertEquals($crs_ref_id, $crs_data->getCrsRefId());
        $this->assertEquals($date, $crs_data->getBeginDate());
        $this->assertSame($date, $crs_data->getBeginDate());
        $this->assertEquals($xbkm_ref_id, $crs_data->getModalitiesInfos()["xbkm_ref_id"]);
        $this->assertEquals($cancellation, $crs_data->getModalitiesInfos()["cancellation"]);
    }
}
