<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace CaT\Plugins\BookingModalities\CourseActions;

use CaT\Ente\Entity;
use ILIAS\TMS\ActionBuilderBase;

/**
 * This is an information about a course action, noteworthy for a user in some context.
 */
class ActionBuilder extends ActionBuilderBase
{
    protected function getSearchActions(bool $with_recommendation_action) : array
    {
        return [
            5 => new AnonymousLogin($this->entity, $this->owner, $this->user, 5),
            10 => new SelfBookCourse($this->entity, $this->owner, $this->user, 10),
            20 => new SelfBookWaiting($this->entity, $this->owner, $this->user, 20),
            30 => new SelfBookRequest($this->entity, $this->owner, $this->user, 30),
            40 => new SelfBookCourseWithApproval($this->entity, $this->owner, $this->user, 40),
            50 => new SelfBookWaitingWithApproval($this->entity, $this->owner, $this->user, 50)
        ];
    }

    protected function getUserBookingActions(bool $with_recommendation_action) : array
    {
        return [
            20 => new SelfCancelCourse($this->entity, $this->owner, $this->user, 20),
            21 => new SelfCancelWaiting($this->entity, $this->owner, $this->user, 21)
        ];
    }

    protected function getEmployeeBookingActions(bool $with_recommendation_action) : array
    {
        return [
            20 => new SuperiorCancelCourse($this->entity, $this->owner, $this->user, 20),
            21 => new SuperiorCancelWaiting($this->entity, $this->owner, $this->user, 21)
        ];
    }

    protected function getSuperiorSearchActions(bool $with_recommendation_action) : array
    {
        return [
            10 => new SuperiorBookCourse($this->entity, $this->owner, $this->user, 10),
            20 => new SuperiorBookWaiting($this->entity, $this->owner, $this->user, 20),
            30 => new SuperiorBookRequest($this->entity, $this->owner, $this->user, 30),
            40 => new SuperiorBookCourseWithApproval($this->entity, $this->owner, $this->user, 40),
            50 => new SuperiorBookWaitingWithApproval($this->entity, $this->owner, $this->user, 50)
        ];
    }
}
