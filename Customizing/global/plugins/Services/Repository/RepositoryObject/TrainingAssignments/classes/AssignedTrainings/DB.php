<?php

namespace CaT\Plugins\TrainingAssignments\AssignedTrainings;

interface DB
{
    /**
     * Get a list of AssignedTrainings where user is tutor
     *
     * @param int 	$user_id
     * @param array $filter
     *
     * @return AssignedTrainings[]
     */
    public function getAssignedTrainingsFor($user_id, array $filter);
}
