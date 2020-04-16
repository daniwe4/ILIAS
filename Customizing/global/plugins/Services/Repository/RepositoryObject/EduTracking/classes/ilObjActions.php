<?php

namespace CaT\Plugins\EduTracking;

class ilObjActions
{
    public function __construct(\ilObjEduTracking $object)
    {
        $this->object = $object;
    }

    /**
     * Get the current object
     *
     * @throws \Exception if no object is set
     *
     * @return \ilObjEduTracking
     */
    public function getObject()
    {
        if ($this->object === null) {
            throw new \Exception("No object is set");
        }

        return $this->object;
    }
}
