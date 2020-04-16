<?php

namespace CaT\Plugins\CourseClassification\Options\Topic;

use CaT\Plugins\CourseClassification\Options\OptionBackend;

/**
 * Extended implementation of the backend for topic option
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class TopicBackend extends OptionBackend
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
     * Update an existing option
     *
     * @param array 	$record
     *
     * @return array
     */
    public function update($record)
    {
        $option = $record["option"];
        $assigned_categories_ids = $this->actions->assignedCategoriesFor($option->getId());
        $this->actions->update($option);
        $this->actions->deassignCategory($option->getId());

        if ($option->getCategory()) {
            $this->actions->assignCategory($option->getId(), $option->getCategory()->getId());
        }

        if ($option->getCategory() === null || !in_array($option->getCategory()->getId(), $assigned_categories_ids)) {
            $record["message"][] = "category_changed";
        }

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
        $new_option = $this->actions->create($option->getCaption());

        if ($option->getCategory()) {
            $this->actions->assignCategory($new_option->getId(), $option->getCategory()->getId());
        }

        $record["option"] = $new_option->withCategory($option->getCategory());
        $record["message"][] = "created_succesfull";
        return $record;
    }
}
