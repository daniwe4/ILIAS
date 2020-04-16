<?php
namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use CaT\Historization\Digester\Digester as Digester;

class OvernightsDigester implements Digester
{
    public function digest(array $payload)
    {
        $dates = [];
        foreach ($payload['xoac_reservations'] as $reservation) {
            $dates[] = $reservation->getDate()->get(IL_CAL_DATE);
        }
        return [
            'nights' => $dates,
            'prior_night' => $payload['xoac_priorday'],
            'following_night' => $payload['xoac_followingday']
        ];
    }
}
