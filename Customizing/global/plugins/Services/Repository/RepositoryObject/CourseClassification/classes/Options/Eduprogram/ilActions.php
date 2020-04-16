<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\CourseClassification\Options\Eduprogram;

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
     * @inheritdoc
     */
    public function getTableData() : array
    {
        return $this->db->select();
    }

    /**
     * @inheritdoc
     */
    public function create(string $caption) : Option
    {
        return $this->db->create($caption);
    }

    /**
     * @inheritdoc
     */
    public function update(Option $option)
    {
        $this->db->update($option);
    }

    /**
     * @inheritdoc
     */
    public function delete(int $option_id)
    {
        $this->db->delete($option_id);
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
