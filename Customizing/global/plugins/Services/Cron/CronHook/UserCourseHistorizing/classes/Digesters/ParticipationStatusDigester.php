<?php

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

require_once 'Services/Tracking/classes/class.ilLPStatus.php';

use CaT\Plugins\UserCourseHistorizing\HistCases\HistUserCourse;
use CaT\Historization\Digester\Digester as Digester;

class ParticipationStatusDigester implements Digester
{
    public function digest(array $payload)
    {
        switch ((int) $payload['status']) {
            case \ilLPStatus::LP_STATUS_COMPLETED_NUM:
                return ['participation_status' => HistUserCourse::PARTICIPATION_STATUS_SUCCESSFUL, 'ps_acquired_date' => date('Y-m-d')];
            case \ilLPStatus::LP_STATUS_FAILED_NUM:
                return ['participation_status' => HistUserCourse::PARTICIPATION_STATUS_ABSENT, 'ps_acquired_date' => date('Y-m-d')];
            case \ilLPStatus::LP_STATUS_IN_PROGRESS_NUM:
                return ['participation_status' => HistUserCourse::PARTICIPATION_STATUS_IN_PROGRESS];
            default:
                return ['participation_status' => HistUserCourse::PARTICIPATION_STATUS_NONE];
        }
    }
}
