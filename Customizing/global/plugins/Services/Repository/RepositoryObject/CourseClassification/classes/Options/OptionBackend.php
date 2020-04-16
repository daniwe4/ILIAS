<?php

namespace CaT\Plugins\CourseClassification\Options;

use CaT\Plugins\CourseClassification\TableProcessing\backend;

/**
 * Basic implementation of the backend for any kind of option
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class OptionBackend implements backend
{
    /**
     * @var ilActions
     */
    protected $actions;

    public function __construct(ilActions $actions)
    {
        $this->actions = $actions;
    }

    /**
     * Delete the option in record
     *
     * @param array
     *
     * @return null
     */
    public function delete($record)
    {
        $option = $record["option"];
        $this->actions->delete($option->getId());
    }

    /**
     * Checks option in record if it is valid
     * If not fills key errors with values
     *
     * @param array
     *
     * @return array
     */
    public function valid($record)
    {
        $option = $record["option"];
        if ($option->getCaption() == "" || $option->getCaption() === null) {
            $record["errors"]["caption"][] = "name_empty";
        }
        return $record;
    }

    /**
     * Update an existing option
     *
     * @param array
     *
     * @return array
     */
    public function update($record)
    {
        $option = $record["option"];
        $this->actions->update($option);
        $record["message"][] = "update_succesfull";
        return $record;
    }

    /**
     * Creates a new option
     *
     * @param array
     *
     * @return array
     */
    public function create($record)
    {
        $option = $record["option"];
        $record["option"] = $this->actions->create($option->getCaption());
        $record["message"][] = "created_succesfull";
        return $record;
    }
}
