<?php

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use CaT\Historization\Digester\Digester as Digester;

class TrainerDigester implements Digester
{
    public function digest(array $payload)
    {
        global $DIC;
        $rbac_review = $DIC['rbacreview'];
        $rbac_review->clearCaches();
        return ['tut' => array_map(
            function ($usr_id) {
                return (int) $usr_id;
            },
            $rbac_review->assignedUsers($payload['role_id'])
        )];
    }
}
