<?php

namespace CaT\Plugins\CourseCreation\Requests;

abstract class RequestsGUI
{
    /**
     * Get the closure is table should be filled with
     *
     * @return \Closure
     */
    abstract protected function fillRow();

    /**
     * Get an instanz of the request table gui
     *
     * @param string 	$parent_cmd
     *
     * @return ilRequestsTableGUI
     */
    protected function getRequestTableGUI($parent_cmd)
    {
        return new ilRequestsTableGUI($this, $parent_cmd, $this->fillRow());
    }
}
