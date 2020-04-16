<?php

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use CaT\Historization\Digester\Digester as Digester;

abstract class PresentSessionDigester implements Digester
{
    public function digest(array $payload)
    {
        $session = $this->getSessionByPayload($payload);
        $crs_id = $this->getCrsIdByPayload($payload);

        $app = $session->getFirstAppointment();
        $fullday = $app->enabledFullTime() === 1;
        if (!$fullday) {
            if ($app->getDaysOffset() !== null) {
                $start_time = $app->getStartingTime();
                $end_time = $app->getEndingTime();
            } else {
                $start_time = (int) $app->getStart()->get(IL_CAL_UNIX);
                $end_time = (int) $app->getEnd()->get(IL_CAL_UNIX);
            }
        } else {
            $start_time = 0;
            $end_time = 0;
        }
        $begin_date = $app->getStart()->get(IL_CAL_DATE);

        $end_date = $app->getEnd()->get(IL_CAL_DATE);
        $return = [
            'session_id' => $session->getId(),
            'crs_id' => $crs_id,
            'removed' => false,
            'fullday' => $fullday,
            'begin_date' => $begin_date,
            'end_date' => $end_date,
            'start_time' => $start_time,
            'end_time' => $end_time
        ];
        return $return;
    }

    abstract protected function getSessionByPayload(array $payload);
    abstract protected function getCrsIdByPayload(array $payload);
}
