<?php

namespace CaT\Plugins\TrainingAdminOverview\AdministratedTrainings;

interface DB
{
    /**
     * Get a list of AdministratedTrainings where user is tutor
     *
     * @param int 	$user_id
     * @param string[] 	$filter
     *
     * @return AdministratedTraining[]
     */
    public function getAdministratedTrainingsFor($user_id, array $filter);
}
