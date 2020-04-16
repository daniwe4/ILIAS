<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\CourseClassification\Options\Category;

use CaT\Plugins\CourseClassification\Options\ilActions as ilOptionActions;
use CaT\Plugins\CourseClassification\Options\Option;
use CaT\Plugins\CourseClassification\Options\Topic;

class ilActions extends ilOptionActions
{
    /**
     * @var ilDB
     */
    protected $db;

    /**
     * @var \ilAppEventHandler
     */
    protected $app_event_handler;

    public function __construct(
        \ilCourseClassificationPlugin $plugin_object,
        ilDB $db,
        \ilAppEventHandler $app_event_handler
    ) {
        $this->plugin_object = $plugin_object;
        $this->db = $db;
        $this->app_event_handler = $app_event_handler;
    }

    /**
     * @inheritoc
     */
    public function getTableData() : array
    {
        return $this->db->select();
    }

    /**
     * @inheritoc
     */
    public function create(string $caption) : Option
    {
        return $this->db->create($caption);
    }

    /**
     * @inheritoc
     */
    public function update(Option $option)
    {
        $this->db->update($option);
    }

    /**
     * @inheritoc
     */
    public function delete(int $category_id)
    {
        $this->db->delete($category_id);
    }

    /**
     * @inheritoc
     */
    public function getNewOption() : Option
    {
        return new Category(-1, "", array());
    }

    public function getTopicsById(int $category_id) : array
    {
        return $this->db->getTopicsById($category_id);
    }

    public function getAffectedCCObjIds(int $id) : array
    {
        $topics_ids = array_map(
            function ($topic) {
                return $topic->getId();
            },
            $this->getTopicsById($id)
        );

        /** @var Topic\ilActions $topic_actions */
        $topic_actions = $this->plugin_object->getActionsByType('Topic');
        $res = [];
        foreach ($topics_ids as $id) {
            $res = array_merge($res, $topic_actions->getAffectedCCObjIds($id));
        }
        return $res;
    }

    /**
     * @inheritdoc
     */
    protected function getEventHandler() : \ilAppEventHandler
    {
        return $this->app_event_handler;
    }
}
