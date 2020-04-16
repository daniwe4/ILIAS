<?php

namespace CaT\Plugins\TrainingAdminOverview;

class ilObjActions
{

    /**
     * @var AdministratedTrainings\DB
     */
    protected $assigned_trainings_db;

    public function __construct(\ilObjTrainingAdminOverview $object, AdministratedTrainings\DB $administrated_trainings_db)
    {
        $this->object = $object;
        $this->administrated_trainings_db = $administrated_trainings_db;
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
     * Get a list of AssignedTrainings where user is admin
     *
     * @param int 	$user_id
     * @param string[] 	$filter
     *
     * @return AssignedTrainings[]
     */
    public function getAdministratedTrainingsFor($user_id, array $filter)
    {
        return $this->administrated_trainings_db->getAdministratedTrainingsFor($user_id, $filter);
    }
}
