<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\CourseClassification\Options\Topic;

use CaT\Plugins\CourseClassification\Options\ilActions as ilOptionActions;
use CaT\Plugins\CourseClassification\Options\Option;

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

    public function assignCategory(int $topic_id, int $category_id)
    {
        $this->db->assignCategory($topic_id, $category_id);
    }

    public function deassignCategory(int $topic_id)
    {
        $this->db->deleteAssignmentsBy($topic_id);
    }

    /**
     * @return int[]
     */
    public function assignedCategoriesFor(int $topic_id) : array
    {
        return $this->db->getAssignedCategories($topic_id);
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
    public function delete(int $option_id)
    {
        $this->db->deleteAssignmentsBy($option_id);
        $this->db->delete($option_id);
    }

    /**
     * @return string[]
     */
    public function getCategoriesForForm() : array
    {
        return $this->db->getCategoriesForForm();
    }

    public function getCategoryById(int $category_id) : ?Option
    {
        return $this->db->getCategoryById($category_id);
    }

    /**
     * @inheritoc
     */
    public function getNewOption() : Option
    {
        return new Topic(-1, "", null);
    }

    /**
     * @inheritdoc
     */
    public function getAffectedCCObjIds(int $id) : array
    {
        return $this->db->getAffectedCCObjectObjIds($id);
    }

    /**
     * @inheritdoc
     */
    protected function getEventHandler() : \ilAppEventHandler
    {
        return $this->app_event_handler;
    }
}
