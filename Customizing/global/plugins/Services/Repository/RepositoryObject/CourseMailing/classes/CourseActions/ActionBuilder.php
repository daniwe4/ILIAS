<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace CaT\Plugins\CourseMailing\CourseActions;

use CaT\Ente\Entity;
use ILIAS\TMS\ActionBuilderBase;

/**
 * This is an information about a course action, noteworthy for a user in some context.
 */
class ActionBuilder extends ActionBuilderBase
{
    protected function getMyTrainingActions(bool $with_recommendation_action) : array
    {
        return [
            300 => new ToMailingLog($this->entity, $this->owner, $this->user, 300),
            350 => new ToMailMembers($this->entity, $this->owner, $this->user, 350),
            150 => new ToInviteSystem($this->entity, $this->owner, $this->user, 150)
        ];
    }

    protected function getMyAdministratedTrainingActions(bool $with_recommendation_action) : array
    {
        return [
            300 => new ToMailingLog($this->entity, $this->owner, $this->user, 300),
            350 => new ToMailMembers($this->entity, $this->owner, $this->user, 350),
            150 => new ToInviteSystem($this->entity, $this->owner, $this->user, 150)
        ];
    }

    protected function getTepSessionDetailActions(bool $with_recommendation_action) : array
    {
        return [
            300 => new ToMailingLog($this->entity, $this->owner, $this->user, 300),
            350 => new ToMailMembers($this->entity, $this->owner, $this->user, 350)
        ];
    }
}
