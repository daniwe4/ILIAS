<?php
namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use CaT\Historization\Digester\Digester as Digester;

abstract class RemoveSessionDigester implements Digester
{
    public function digest(array $payload)
    {
        $session_id = $this->getSesionIdByPalyoad($payload);
        $crs_id = $this->getCrsIdByPayload($payload);

        $return = [
            'session_id' => $session_id,
            'crs_id' => $crs_id,
            'removed' => true,
            'fullday' => false,
            'begin_date' => '0001-01-01',
            'end_date' => '0001-01-01',
            'start_time' => 0,
            'end_time' => 0
        ];
        return $return;
    }

    abstract protected function getSesionIdByPalyoad(array $payload);
    abstract protected function getCrsIdByPayload(array $payload);
}
