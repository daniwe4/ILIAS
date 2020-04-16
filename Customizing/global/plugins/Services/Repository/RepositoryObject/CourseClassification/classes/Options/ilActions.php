<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\CourseClassification\Options;

abstract class ilActions
{
    /**
     * @var \ilCourseClassificationPlugin
     */
    protected $plugin_object;

    abstract public function getTableData() : array;

    abstract public function create(string $caption) : Option;

    abstract public function update(Option $option);

    abstract public function delete(int $option_id);

    abstract protected function getAffectedCCObjIds(int $id) : array;

    abstract protected function getEventHandler() : \ilAppEventHandler;

    public function getPlugin() : \ilCourseClassificationPlugin
    {
        return $this->plugin_object;
    }

    public function getNewOption() : Option
    {
        return new Option(-1, "");
    }

    public function raiseUpdateEventFor(int $id)
    {
        $e = array();
        $e["cc_obj_ids"] = $this->getAffectedCCObjIds($id);
        $this->getEventHandler()->raise("Plugin/CourseClassification", "updateCCStatic", $e);
    }
}
