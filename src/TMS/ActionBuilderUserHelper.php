<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace ILIAS\TMS;

/**
 * Useful functions to process CourseAction.
 */
trait ActionBuilderUserHelper
{
    /**
     * Get components for the entity.
     *
     * @param string $component_type
     * @return ActionBuilder[]
     */
    abstract public function getComponentsOfType($component_type);

    /**
     * Get information for a certain context ordered by priority.
     *
     * @return ActionBuilder[]
     */
    public function getActionBuilders()
    {
        return $this->getComponentsOfType(ActionBuilder::class);
    }

    /**
     * @return ActionBuilder[]
     */
    protected function getActionBuilder() : array
    {
        if (is_null($this->action_builders)) {
            $this->action_builders = $this->getActionBuilders();
        }
        return $this->action_builders;
    }
}
