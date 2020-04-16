<?php

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use CaT\Plugins\UserCourseHistorizing\HistCases\HistUserCourse;
use CaT\Historization\Digester\Digester as Digester;

require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/CourseClassification/classes/class.ilObjCourseClassification.php';

class CourseClassificationDigester implements Digester
{
    protected $event;

    public function __construct($event)
    {
        assert('is_string($event)');
        $this->event = $event;
    }

    public function digest(array $payload)
    {
        switch ($this->event) {
            case 'insertNode':
                return $this->digestInsert($payload);
            case 'updateCCObject':
            case 'updateCCStatic':
                return $this->digestUpdate($payload);
        }
    }

    protected function digestInsert(array $payload)
    {
        global $DIC;
        foreach ($DIC['tree']->getNodePath($payload['parent_id']) as $node) {
            if ($node['type'] === 'crs') {
                $crs_id = $node['obj_id'];
                break;
            }
        }
        $return = $this->getCCDataByRefId((int) $payload['node_id']);
        $return['crs_id'] = $crs_id;
        return $return;
    }

    protected function digestUpdate($payload)
    {
        return $this->getCCDataByRefId($payload['cc_ref_id']);
    }

    public function getCCDataByRefId($ref_id)
    {
        assert('is_int($ref_id)');
        $cc_obj = new \ilObjCourseClassification($ref_id);
        list($type_id, $type, $target_group_ids, $target_group, $goals, $topic_ids, $topics) = $cc_obj->getCourseClassificationValues();
        $return = [];
        $return['topics'] = is_null($topics) ? [] : $topics ;
        $return['crs_type'] = $type;
        $actions = $cc_obj->getActions();
        $classifications = $cc_obj->getCourseClassification();
        $return['edu_programme'] = array_shift($actions->getEduProgramName($classifications->getEduProgram()));
        $return['categories'] = $actions->getCategoryNames($actions->getCategoriesByTopicIds($topic_ids));
        $tg = [];
        if (!is_null($target_group)) {
            $tg = $target_group;
        }
        $return['target_groups'] = $tg;
        return $return;
    }
}
