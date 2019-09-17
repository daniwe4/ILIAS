<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

use ILIAS\TMS\ActionBuilderBase;

/**
 * This is an information about a course action, noteworthy for a user in some context.
 */
class TMSActionBuilder extends ActionBuilderBase
{
    protected function getSearchActions(bool $with_recommendation_action) : array
    {
        $ret = [];

        if ($with_recommendation_action) {
            array_push(
                $ret,
                new SendRecommendationMail($this->entity, $this->owner, $this->user, 1500)
            );
        }

        return $ret;
    }

    protected function getUserBookingActions(bool $with_recommendation_action) : array
    {
        $ret = [
            new ToCourse($this->entity, $this->owner, $this->user, 100)
        ];

        if ($with_recommendation_action) {
            array_push(
                $ret,
                new SendRecommendationMail($this->entity, $this->owner, $this->user, 1500)
            );
        }

        return $ret;
    }

    protected function getEmployeeBookingActions(bool $with_recommendation_action) : array
    {
        $ret = [
            new ToCourse($this->entity, $this->owner, $this->user, 100)
        ];

        if ($with_recommendation_action) {
            array_push(
                $ret,
                new SendRecommendationMail($this->entity, $this->owner, $this->user, 1500)
            );
        }

        return $ret;
    }

    protected function getMyTrainingActions(bool $with_recommendation_action) : array
    {
        return [
            new ToCourse($this->entity, $this->owner, $this->user, 100),
            new ToCourseMemberTab($this->entity, $this->owner, $this->user, 200),
            new CancelCourse($this->entity, $this->owner, $this->user, 600)
        ];
    }

    protected function getMyAdministratedTrainingActions(bool $with_recommendation_action) : array
    {
        return [
            new ToCourse($this->entity, $this->owner, $this->user, 100),
            new ToCourseMemberTab($this->entity, $this->owner, $this->user, 200),
            new CancelCourse($this->entity, $this->owner, $this->user, 600)
        ];
    }

    protected function getSuperiorSearchActions(bool $with_recommendation_action) : array
    {
        $ret = [];

        if ($with_recommendation_action) {
            array_push(
                $ret,
                new SendRecommendationMail($this->entity, $this->owner, $this->user, 1500)
            );
        }

        return $ret;
    }

    protected function getTepSessionDetailActions(bool $with_recommendation_action) : array
    {
        return [
            new ToCourse($this->entity, $this->owner, $this->user, 100),
            new ToCourseMemberTab($this->entity, $this->owner, $this->user, 200)
        ];
    }
}
