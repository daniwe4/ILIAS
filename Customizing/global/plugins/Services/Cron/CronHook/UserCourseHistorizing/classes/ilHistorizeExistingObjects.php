<?php

namespace CaT\Plugins\UserCourseHistorizing;

/**
 * Historize existing courses with historizing required plugins
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilHistorizeExistingObjects
{
    public function __construct()
    {
        global $DIC;
        $this->g_objDefinition = $DIC["objDefinition"];
        $this->g_db = $DIC->database();
        $this->g_tree = $DIC->repositoryTree();
    }

    public function historize()
    {
        foreach ($this->getAllCourses() as $crs) {
            $crs->update();

            $this->accomodation($crs);
            $this->courseMember($crs);
            $this->courseClassification($crs);
            $this->eduTracking($crs);
            $this->bookingModalities($crs);
            $this->courseAccounting($crs);
        }
    }

    public function getAllCourses()
    {
        $query = "SELECT DISTINCT object_data.obj_id, object_reference.ref_id FROM object_data" . PHP_EOL
                . " JOIN object_reference ON object_data.obj_id = object_reference.obj_id" . PHP_EOL
                . " WHERE object_data.type = 'crs'" . PHP_EOL
                . "    AND object_reference.deleted IS NULL";

        $ret = array();
        $res = $this->g_db->query($query);
        while ($row = $this->g_db->fetchAssoc($res)) {
            $ret[] = \ilObjectFactory::getInstanceByRefId((int) $row["ref_id"]);
        }

        return $ret;
    }

    public function accomodation(\ilObjCourse $crs)
    {
        foreach ($this->getAllChildrenOfByType((int) $crs->getRefId(), "xoac") as $key => $accomodation) {
            $actions = $accomodation->getActions();
            $actions->raiseUpdateEvent();

            $user_ids = $actions->getAllUserIdsWithReservationsAtObj();
            foreach ($user_ids as $user_id) {
                $actions->raiseReservationUpdateEventFor($user_id);
            }
        }
    }

    public function courseMember(\ilObjCourse $crs)
    {
        $last_edited = [];
        foreach ($this->getAllChildrenOfByType((int) $crs->getRefId(), "xcmb") as $key => $course_member) {
            if ($course_member->getSettings()->getClosed() === true) {
                foreach ($course_member->getActions()->getMemberWithSavedLPSatus() as $member) {
                    $course_member->getActions()->throwEvent((int) $crs->getId(), (int) $member->getUserId(), (int) $member->getIDDLearningTime());
                    $last_edited[] = $member->getLastEdited()->get(IL_CAL_DATE);
                }
                if (rsort($last_edited)) {
                    $finalized_date = array_shift($last_edited);
                }
            }
        }
    }

    public function courseClassification(\ilObjCourse $crs)
    {
        foreach ($this->getAllChildrenOfByType((int) $crs->getRefId(), "xccl") as $key => $course_classification) {
            $course_classification->getActions()->raiseUpdateEvent();
        }
    }

    public function eduTracking(\ilObjCourse $crs)
    {
        foreach ($this->getAllChildrenOfByType((int) $crs->getRefId(), "xetr") as $key => $edu_tracking) {
            $actions = $edu_tracking->getActionsFor("IDD");
            $settings = $actions->select();
            $minutes = $settings->getMinutes();

            if (!is_null($minutes)) {
                $settings->throwEvent("updateIDD", (int) $crs->getId(), $minutes);
            }

            $actions = $edu_tracking->getActionsFor("GTI");
            $settings = $actions->select();
            $minutes = $settings->getMinutes();

            if (!is_null($minutes)) {
                $settings->throwEvent("updateGTI", (int) $crs->getId(), $minutes);
            }
        }
    }

    public function courseAccounting(\ilObjCourse $crs)
    {
        foreach ($this->getAllChildrenOfByType((int) $crs->getRefId(), "xacc") as $key => $accounting) {
            $accounting->getObjectActions()->updatedEvent();
        }
    }

    public function bookingModalities(\ilObjCourse $crs)
    {
        foreach ($this->getAllChildrenOfByType((int) $crs->getRefId(), "xbkm") as $key => $booking_modalities) {
            $booking_modalities->doRead();
            $booking_modalities->update();
        }
    }

    /**
     * Get all children by type recursive
     *
     * @param int 	$ref_id
     * @param string 	$search_type
     *
     * @return Object 	of search type
     */
    public function getAllChildrenOfByType($ref_id, $search_type)
    {
        $childs = $this->g_tree->getChilds($ref_id);
        $ret = array();

        foreach ($childs as $child) {
            $type = $child["type"];
            if ($type == $search_type) {
                $ret[] = \ilObjectFactory::getInstanceByRefId($child["child"]);
            }

            if ($this->g_objDefinition->isContainer($type)) {
                $rec_ret = $this->getAllChildrenOfByType($child["child"], $search_type);
                if (!is_null($rec_ret)) {
                    $ret = array_merge($ret, $rec_ret);
                }
            }
        }

        return $ret;
    }

    /**
     * Get first child by type recursive
     *
     * @param int 	$ref_id
     * @param string 	$search_type
     *
     * @return Object 	of search type
     */
    protected function getFirstChildOfByType($ref_id, $search_type)
    {
        $childs = $this->g_tree->getChilds($ref_id);

        foreach ($childs as $child) {
            $type = $child["type"];
            if ($type == $search_type) {
                return \ilObjectFactory::getInstanceByRefId($child["child"]);
            }

            if ($this->g_objDefinition->isContainer($type)) {
                $ret = $this->getFirstChildOfByType($child["child"], $search_type);
                if (!is_null($ret)) {
                    return $ret;
                }
            }
        }

        return null;
    }
}
