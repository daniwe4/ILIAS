<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use PHPUnit\Framework\TestCase;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistUserCourse;

class ParticipationStatusDigesterTest extends TestCase
{
    private static $status = [
        \ilLPStatus::LP_STATUS_COMPLETED_NUM,
        \ilLPStatus::LP_STATUS_FAILED_NUM,
        \ilLPStatus::LP_STATUS_IN_PROGRESS_NUM,
        100 // force default case
    ];

    public function testDigest() : void
    {
        foreach (self::$status as $s) {
            $payload = [
                'status' => $s
            ];

            $date = date('Y-m-d');

            $obj = new ParticipationStatusDigester();
            $result = $obj->digest($payload);

            switch ($s) {
                case \ilLPStatus::LP_STATUS_COMPLETED_NUM:
                    $this->assertEquals(
                        HistUserCourse::PARTICIPATION_STATUS_SUCCESSFUL,
                        $result['participation_status']
                    );
                    $this->assertEquals($date, $result['ps_acquired_date']);
                    break;
                case \ilLPStatus::LP_STATUS_FAILED_NUM:
                    $this->assertEquals(
                        HistUserCourse::PARTICIPATION_STATUS_ABSENT,
                        $result['participation_status']
                    );
                    $this->assertEquals($date, $result['ps_acquired_date']);
                    break;
                case \ilLPStatus::LP_STATUS_IN_PROGRESS_NUM:
                    $this->assertEquals(
                        HistUserCourse::PARTICIPATION_STATUS_IN_PROGRESS,
                        $result['participation_status']
                    );
                    break;
                default:
                    $this->assertEquals(
                        HistUserCourse::PARTICIPATION_STATUS_NONE,
                        $result['participation_status']
                    );
                    break;
            }
        }
    }
}
