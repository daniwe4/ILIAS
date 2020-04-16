<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\Agenda;

use \CaT\Ente\ILIAS\SeparatedUnboundProvider;
use \CaT\Ente\ILIAS\Entity;
use \ILIAS\TMS\CourseInfo;
use \ILIAS\TMS\CourseInfoImpl;

class UnboundProvider extends SeparatedUnboundProvider
{

    /**
     * @inheritdocs
     */
    public function componentTypes()
    {
        return [
            CourseInfo::class
        ];
    }

    /**
     * Build the component(s) of the given type for the given object.
     *
     * @param   string    $component_type
     * @param   Entity    $provider
     * @return  CourseInfo[]
     */
    public function buildComponentsOf($component_type, Entity $entity)
    {
        if ($component_type === CourseInfo::class) {
            return $this->getCourseInfos($entity, $this->owner());
        }

        throw new \InvalidArgumentException("Unexpected component type '$component_type'");
    }

    /**
     * Get all possible course infos
     *
     * @param Entity $entity
     * @param \ilObjAgenda $owner
     *
     * @return CourseInfo[]
     */
    protected function getCourseInfos(Entity $entity, \ilObjAgenda $owner)
    {
        $agenda_db = $owner->getAgendaEntryDB();
        $entries = $agenda_db->selectFor((int) $owner->getId());

        $ret = [];
        if (\ilPluginAdmin::isPluginActive('xaip') === true) {
            $obj = \ilPluginAdmin::getPluginObjectById("xaip");
            $actions = $obj->getActions();

            $total_minutes = 0;
            $topics = [];
            foreach ($entries as $key => $entry) {
                $item = $actions->getAgendaItemById((int) $entry->getPoolItemId());
                if ($item->getIddRelevant()) {
                    $total_minutes += $entry->getIDDTime();
                }
                $training_topics = $item->getTrainingTopics();
                if (is_array($training_topics)) {
                    $topics = array_merge($topics, $training_topics);
                }
            }

            $ret[] = new CourseInfoImpl(
                $entity,
                "",
                (int) $total_minutes,
                "",
                100,
                [CourseInfo::CONTEXT_XETR_TIME_INFO]
            );

            $topics = array_unique($topics);
            $ret[] = new CourseInfoImpl(
                $entity,
                "",
                $topics,
                "",
                100,
                [CourseInfo::CONTEXT_XCCL_TOPICS]
            );
        }

        return $ret;
    }
}
