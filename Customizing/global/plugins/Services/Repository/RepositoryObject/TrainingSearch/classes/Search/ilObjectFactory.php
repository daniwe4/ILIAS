<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\TrainingSearch\Search;

/**
 * A factory for some required ilObjects.
 */
class ilObjectFactory
{
    /**
     * @var \ilTree
     */
    protected $tree;

    /**
     * @var	array<int, \ilObjCourseClassification>
     */
    protected $course_classification_cache = [];

    /**
     * @var	array<int, Course>
     */
    protected $course_cache = [];

    public function __construct(
        \ilTree $tree
    ) {
        $this->tree = $tree;
    }

    /**
     * @return Course|null
     */
    public function getCourseFor(int $ref_id, int $obj_id)
    {
        if (isset($this->course_cache[$ref_id])) {
            return $this->course_cache[$ref_id];
        }

        $xccl = $this->getCourseClassificationObjFor($ref_id);
        if (is_null($xccl)) {
            $this->course_cache[$ref_id] = null;
            return null;
        }

        $obj_course = new \ilObjCourse(0, false);
        $obj_course->setId($obj_id);
        $obj_course->setRefId($ref_id);
        $obj_course->read();

        list($_, $city, $address) = $this->getVenueInfos($obj_id);
        list($_, $type, $_, $target_group, $goals, $_, $topics, $_, $category, $content)
            = $xccl->getCourseClassificationValues();

        $course = new Course(
            $obj_course,
            $type,
            $target_group,
            $goals,
            $topics,
            $city,
            $address,
            "KOSTEN"
        );

        $this->course_cache[$ref_id] = $course;

        return $course;
    }

    /**
     * @return \ilObjCourseClassification|null
     */
    public function getCourseClassificationObjFor(int $ref_id)
    {
        if (isset($this->course_classification_cache[$ref_id])) {
            return $this->course_classification_cache[$ref_id];
        }

        $node_data = $this->tree->getNodeData($ref_id);
        $children = $this->tree->getSubTree($node_data, true, "xccl");

        if (count($children) === 0) {
            return null;
        }

        $child = array_shift($children);
        $xccl = \ilObjectFactory::getInstanceByRefId($child["child"]);

        $this->course_classification_cache[$ref_id] = $xccl;

        return $xccl;
    }

    /**
     * @return string[]
     */
    protected function getVenueInfos(int $crs_id) : array
    {
        $plugin = \ilPluginAdmin::getPluginObjectById('venues');
        if (!$plugin) {
            return array(-1,"", "");
        }

        return $plugin->getVenueInfos($crs_id);
    }
}
