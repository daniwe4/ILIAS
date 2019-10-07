<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

class ilTMSCertificatePlaceholderValues
{
    /**
     * @var ilLanguage
     */
    protected $language;

    /**
     * @var ilTree
     */
    protected $tree;

    public function __construct(ilLanguage $language, ilTree $tree)
    {
        $this->language = $language;
        $this->tree = $tree;
    }

    public function getTMSVariablesForPreview() : array
    {
        $ret = array();
        $ret["COURSE_START_DATE"] = ilDatePresentation::formatDate(new ilDate(time() - (24 * 60 * 60 * 10), IL_CAL_UNIX));
        $ret["COURSE_END_DATE"] = ilDatePresentation::formatDate(new ilDate(time() - (24 * 60 * 60 * 5), IL_CAL_UNIX));
        $ret["COURSE_TYPE"] = $this->language->txt("pl_course_type_preview");

        if (\ilPluginAdmin::isPluginActive('xetr')) { //edutracking
            $ret["IDD_TIME"] = $this->language->txt("pl_idd_learning_time_preview");
            $ret["IDD_USER_TIME"] = $this->language->txt("pl_idd_learning_time_user_preview");
        }
        return $ret;
    }

    public function getTMSPlaceholderValues(ilObject $crs, int $user_id) : array
    {
        $ret = array();
        $crs_id = (int) $crs->getId();
        $crs_ref_id = array_shift(ilObject::_getAllReferences($crs_id));
        $crs_ref_id = (int) $crs_ref_id;

        $crs_start = $crs->getCourseStart();
        if ($crs_start === null) {
            $ret["COURSE_START_DATE"] = null;
            $ret["COURSE_END_DATE"] = null;
        } else {
            $crs_end = $crs->getCourseEnd();
            $ret["COURSE_START_DATE"] = ilDatePresentation::formatDate($crs_start);
            $ret["COURSE_END_DATE"] = ilDatePresentation::formatDate($crs_end);
        }

        if (\ilPluginAdmin::isPluginActive('xccl')) { //course classification
            $course_classification = $this->getFirstChildOfByType($crs_ref_id, "xccl");
            if ($course_classification) {
                $cc_actions = $course_classification->getActions();
                $ret["COURSE_TYPE"] = array_shift($cc_actions->getTypeName($course_classification->getCourseClassification()->getType()));
            }
        }

        if (\ilPluginAdmin::isPluginActive('xetr')) { //edutracking
            $edu_tracking = $this->getFirstChildOfByType($crs_ref_id, "xetr");
            if ($edu_tracking) {
                $et_action = $edu_tracking->getActionsFor("IDD");
                $ret["IDD_TIME"] = $this->transformIDDLearningTimeToString($et_action->select()->getMinutes());
            }

            $course_member = $this->getFirstChildOfByType($crs_ref_id, "xcmb");
            if ($course_member) {
                $cmb_actions = $course_member->getActions();
                $ret["IDD_USER_TIME"] = $this->transformIDDLearningTimeToString($cmb_actions->getMinutesFor($user_id));
            }
        }

        return $ret;
    }

    protected function transformIDDLearningTimeToString(int $minutes) : string
    {
        $hours = floor($minutes / 60);
        $minutes = $minutes - $hours * 60;
        return str_pad((string) $hours, 2, "0", STR_PAD_LEFT) . ":" . str_pad((string) $minutes, 2, "0", STR_PAD_LEFT);
    }

    /**
     * @param int $ref_id
     * @param string $search_type
     * @return ilObject | null
     * @throws ilDatabaseException
     * @throws ilObjectNotFoundException
     */
    protected function getFirstChildOfByType(int $ref_id, string $search_type)
    {
        $children = $this->tree->getSubTree(
            $this->tree->getNodeData($ref_id),
            false,
            $search_type
        );

        if (count($children) == 0) {
            return null;
        }

        $child = array_shift($children);
        return \ilObjectFactory::getInstanceByRefId($child);
    }
}
