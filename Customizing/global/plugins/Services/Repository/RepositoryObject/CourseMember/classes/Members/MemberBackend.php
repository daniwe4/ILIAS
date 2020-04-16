<?php

namespace CaT\Plugins\CourseMember\Members;

use CaT\Plugins\CourseMember\TableProcessing\backend;
use CaT\Plugins\CourseMember\ilObjActions;

/**
 * Basic implementation of the backend for any kind of object
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class MemberBackend implements backend
{
    /**
     * @var ilObjActions
     */
    protected $actions;

    public function __construct(ilObjActions $actions, $idd_learning_time, $credits = null)
    {
        $this->actions = $actions;
        $this->credits = $credits;
        $this->idd_learning_time = $idd_learning_time;
    }

    /**
     * @inheritdoc
     */
    public function delete(array $record)
    {
        return $record;
    }

    /**
     * @inheritdoc
     */
    public function valid(array $record)
    {
        $object = $record["object"];
        if (!is_null($this->credits) && $object->getCredits() > $this->credits) {
            $record["errors"]["credit"][] = "credits_to_high";
        }
        if (!is_null($this->idd_learning_time) && $object->getIDDLearningTime() > $this->idd_learning_time) {
            $record["errors"]["learning_time"][] = "idd_learning_time_to_high";
        }
        return $record;
    }

    /**
     * @inheritdoc
     */
    public function update(array $record)
    {
        $object = $record["object"];
        $record["object"] = $this->actions->upsert($object);
        $record["message"][] = "update_succesfull";
        return $record;
    }

    /**
     * @inheritdoc
     */
    public function create(array $record)
    {
        $object = $record["object"];
        $record["object"] = $this->actions->upsert($object);
        $record["message"][] = "update_succesfull";
        return $record;
    }
}
