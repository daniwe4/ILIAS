<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace CaT\Plugins\Accomodation\CourseActions;

use ILIAS\TMS\ActionBuilderBase;

/**
 * This is an information about a course action, noteworthy for a user in some context.
 */
class ActionBuilder extends ActionBuilderBase
{
    protected function getMyTrainingActions(bool $with_recommendation_action) : array
    {
        return [
            400 => new ToAccomodationSettings($this->entity, $this->owner, $this->user, 400)
        ];
    }

    protected function getMyAdministratedTrainingActions(bool $with_recommendation_action) : array
    {
        return [
            400 => new ToAccomodationSettings($this->entity, $this->owner, $this->user, 400)
        ];
    }

    protected function getTepSessionDetailActions(bool $with_recommendation_action) : array
    {
        return [
            400 => new ToAccomodationSettings($this->entity, $this->owner, $this->user, 400)
        ];
    }
}
