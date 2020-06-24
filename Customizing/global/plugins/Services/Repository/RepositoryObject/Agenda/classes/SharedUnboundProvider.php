<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\Agenda;

use \CaT\Ente\ILIAS\SharedUnboundProvider as Base;
use \CaT\Ente\ILIAS\Entity;
use \ILIAS\TMS\CourseCreation as CC;

class SharedUnboundProvider extends Base
{
    /**
     * @inheritDoc
     */
    public function componentTypes()
    {
        return [CC\Step::class];
    }

    /**
     * @inheritDoc
     */
    public function buildComponentsOf($component_type, Entity $entity)
    {
        assert(is_string($component_type));
        if ($component_type === CC\Step::class) {
            return $this->getCourseCreationSteps($entity);
        }
        throw new \InvalidArgumentException("Unexpected component type '$component_type'");
    }

    /**
     * Get all possible course creation steps
     *
     * @param Entity 	$entity
     * @return CC\Step[]
     */
    protected function getCourseCreationSteps(Entity $entity) : array
    {
        $owners = $this->getOwnersSortedBySessionStart();
        $ret = array();
        foreach ($owners as $key => $owner) {
            $ret[] = new CourseCreation\AgendaStep($entity, $owner, $key);
        }

        return $ret;
    }


    /**
     * Get all owner sorted by session start date
     *
     * @return []
     */
    protected function getOwnersSortedBySessionStart() : array
    {
        $owners = $this->owners();
        usort($owners, function ($l, $r) {
            $l = $l->getSessionStart();
            $r = $r->getSessionStart();

            if ($l === $r) {
                return 0;
            }

            if ($l === null) {
                return 1;
            }

            if ($r === null) {
                return -1;
            }

            if ($l > $r) {
                return 1;
            }

            return -1;
        });

        return $owners;
    }
}
