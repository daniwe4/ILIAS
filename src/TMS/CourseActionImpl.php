<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace ILIAS\TMS;

use CaT\Ente;
use ILIAS\UI;
use ILIAS\UI\Implementation\Component\Modal\Modal;

/**
 * This is an information about a course action, noteworthy for a user in some context.
 */
abstract class CourseActionImpl implements CourseAction
{

    /**
     * @var	Ente\Entity
     */
    protected $entity;

    /**
     * @var	\ilObject
     */
    protected $owner;

    /**
     * @var int
     */
    protected $priority;

    /**
     * @var	int[]
     */
    protected $contexts;

    /**
     * @var int
     */
    protected $current_user_id;

    /**
     * @param \ilObjUser 	$current_user will most probably be the global user.
     * @param int[] 	$contexts
     */
    public function __construct(Ente\Entity $entity, \ilObject $owner, \ilObjUser $current_user, int $priority, array $contexts)
    {
        $this->entity = $entity;
        $this->owner = $owner;
        $this->priority = $priority;
        $this->contexts = $contexts;

        $this->current_user_id = $current_user->getId();
    }

    /**
     * @inheritdoc
     */
    public function entity()
    {
        return $this->entity;
    }

    /**
     * @inheritdoc
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @inheritdoc
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @inheritdoc
     */
    public function hasContext($context)
    {
        return in_array($context, $this->contexts);
    }

    /**
     * @inheritdoc
     */
    abstract public function isAllowedFor($usr_id);

    /**
     * @inheritdoc
     */
    abstract public function getLink(\ilCtrl $ctrl, $usr_id);

    /**
     * @inheritdoc
     */
    abstract public function getLabel();

    /**
     * @inheritDoc
     */
    public function hasModal()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getModal(\ilCtrl $ctrl, UI\Factory $factory, int $usr_id) : Modal
    {
        throw new \LogicException("No modal provided");
    }
}
