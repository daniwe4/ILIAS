<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace CaT\Plugins\Webinar\CourseActions;

use ILIAS\TMS\ActionBuilderBase;

/**
 * This is an information about a course action, noteworthy for a user in some context.
 */
class ActionBuilder extends ActionBuilderBase
{
    protected function getMyTrainingActions(bool $with_recommendation_action) : array
    {
        return [
            500 => new ToWebinarSettings($this->entity, $this->owner, $this->user, 500)
        ];
    }

    protected function getMyAdministratedTrainingActions(bool $with_recommendation_action) : array
    {
        return [
            500 => new ToWebinarSettings($this->entity, $this->owner, $this->user, 500)
        ];
    }

    protected function getTepSessionDetailActions(bool $with_recommendation_action) : array
    {
        return [
            500 => new ToWebinarSettings($this->entity, $this->owner, $this->user, 500)
        ];
    }

    protected function getUserBookingActions(bool $with_recommendation_action) : array
    {
        $action = new ToWebinarUrl($this->entity, $this->owner, $this->user, 501);
        if (trim($action->getConfiguredLink()) !== '') {
            return [501 => $action];
        }
        return [];
    }
}
