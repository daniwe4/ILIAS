<?php

namespace CaT\Plugins\TrainingAssignments;

class ilObjActions
{

    /**
     * @var AssignedTrainings\DB
     */
    protected $assigned_trainings_db;

    public function __construct(\ilObjTrainingAssignments $object, AssignedTrainings\DB $assigned_trainings_db)
    {
        $this->object = $object;
        $this->assigned_trainings_db = $assigned_trainings_db;
    }

    /**
     * Get instance of current object
     *
     * @throws Exception 	if no object is set
     * @return \ilObjUserBookings
     */
    public function getObject()
    {
        if ($this->object === null) {
            throw new \Exception("No object was set");
        }

        return $this->object;
    }

    /**
     * Get a list of AssignedTrainings where user is tutor
     *
     * @param int 	$user_id
     * @param array $filter
     *
     * @return AssignedTrainings[]
     */
    public function getAssignedTrainingsFor($user_id, array $filter)
    {
        return $this->assigned_trainings_db->getAssignedTrainingsFor($user_id, $filter);
    }
}
