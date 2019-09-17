<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace ILIAS\TMS;

use CaT\Ente\Entity;

/**
 * Base to get course actions for separate contexts
 */
class ActionBuilderBase implements ActionBuilder
{
    /**
     * @var Entity
     */
    protected $entity;

    /**
     * @var \ilObject
     */
    protected $owner;

    /**
     * @var \ilObjUser
     */
    protected $user;

    public function __construct(Entity $entity, \ilObject $owner, \ilObjUser $user)
    {
        $this->entity = $entity;
        $this->owner = $owner;
        $this->user = $user;
    }

    /**
     * @inheritDoc
     */
    public function entity()
    {
        return $this->entity;
    }

    /**
     * @inheritDoc
     */
    public function getCourseActionsFor(
        int $context,
        int $usr_id,
        bool $with_recommendation_action = true
    ) : array {
        switch ($context) {
            case self::CONTEXT_SEARCH:
                $actions = $this->getSearchActions($with_recommendation_action);
                break;
            case self::CONTEXT_USER_BOOKING:
                $actions = $this->getUserBookingActions($with_recommendation_action);
                break;
            case self::CONTEXT_EMPLOYEE_BOOKING:
                $actions = $this->getEmployeeBookingActions($with_recommendation_action);
                break;
            case self::CONTEXT_EDU_BIO:
                $actions = $this->getEduBiographyActions($with_recommendation_action);
                break;
            case self::CONTEXT_EMPOYEE_EDU_BIO:
                $actions = $this->getEmployeeEduBiographyActions($with_recommendation_action);
                break;
            case self::CONTEXT_MY_TRAININGS:
                $actions = $this->getMyTrainingActions($with_recommendation_action);
                break;
            case self::CONTEXT_MY_ADMIN_TRAININGS:
                $actions = $this->getMyAdministratedTrainingActions($with_recommendation_action);
                break;
            case self::CONTEXT_SUPERIOR_SEARCH:
                $actions = $this->getSuperiorSearchActions($with_recommendation_action);
                break;
            case self::CONTEXT_TEP_SESSION_DETAILS:
                $actions = $this->getTepSessionDetailActions($with_recommendation_action);
                break;
            default:
                throw new Exception("Unknown context: " . $context);
        }

        $actions = $this->sortActionsByPriority($actions);
        return $this->filterActionsByUserId($actions, $usr_id);
    }

    protected function sortActionsByPriority(array $actions) : array
    {
        uasort(
            $actions,
            function (CourseAction $a, CourseAction $b) {
                if ($a->getPriority() > $b->getPriority()) {
                    return 1;
                }

                if ($b->getPriority() > $a->getPriority()) {
                    return -1;
                }

                return 0;
            }
        );

        return $actions;
    }

    /**
     * @param CourseAction[] 	$actions
     * @return CourseAction[]
     */
    protected function filterActionsByUserId(array $actions, int $usr_id) : array
    {
        $actions = array_filter(
            $actions,
            function (CourseAction $action) use ($usr_id) {
                return $action->isAllowedFor($usr_id);
            }
        );

        return $actions;
    }

    protected function getSearchActions(bool $with_recommendation_action) : array
    {
        return [];
    }

    protected function getUserBookingActions(bool $with_recommendation_action) : array
    {
        return [];
    }

    protected function getEmployeeBookingActions(bool $with_recommendation_action) : array
    {
        return [];
    }

    protected function getEduBiographyActions(bool $with_recommendation_action) : array
    {
        return [];
    }

    protected function getEmployeeEduBiographyActions(bool $with_recommendation_action) : array
    {
        return [];
    }

    protected function getMyTrainingActions(bool $with_recommendation_action) : array
    {
        return [];
    }

    protected function getMyAdministratedTrainingActions(bool $with_recommendation_action) : array
    {
        return [];
    }

    protected function getSuperiorSearchActions(bool $with_recommendation_action) : array
    {
        return [];
    }

    protected function getTepSessionDetailActions(bool $with_recommendation_action) : array
    {
        return [];
    }
}
