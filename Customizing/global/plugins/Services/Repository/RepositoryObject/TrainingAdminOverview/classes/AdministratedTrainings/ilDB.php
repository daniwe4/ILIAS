<?php

namespace CaT\Plugins\TrainingAdminOverview\AdministratedTrainings;

/**
 * Implementation for ILIAS to get all courses user can administrate
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilDB implements DB
{
    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var \ilObjUser
     */
    protected $user;

    /**
     * @var \ilAccessHandler
     */
    protected $access;

    public function __construct(\ilDBInterface $db, \ilAccessHandler $access, \ilTree $tree, \ilObjectDefinition $objDefinition)
    {
        $this->db = $db;
        $this->access = $access;
        $this->tree = $tree;
        $this->objDefinition = $objDefinition;
    }

    /**
     * @inheritdoc
     */
    public function getAdministratedTrainingsFor(int $user_id, array $filter)
    {
        $ret = array();
        $crs_ids = $this->getCourseIdsWhereUserIsAdmin($user_id);

        foreach ($crs_ids as $key => $crs_id) {
            $ref_id = (int) $this->getRefId($crs_id);
            $crs = \ilObjectFactory::getInstanceByRefId($ref_id);
            $crs_end = $crs->getCourseEnd();

            if ($this->endDatePassed($crs_end) && !$this->userHasAccess($user_id, $ref_id, $crs_id)) {
                continue;
            }

            if ($this->catchedByFilter($crs, $filter)) {
                continue;
            }

            $ret[] = $this->createAdministratedTraining($ref_id, $crs->getTitle(), $crs->getCourseStart());
        }

        return $ret;
    }

    /**
     * Get course ids where user is admin
     *
     * @param int $user_id
     *
     * @return int[]
     */
    protected function getCourseIdsWhereUserIsAdmin($user_id)
    {
        $obj_ids = array();
        $query = "SELECT DISTINCT obd.obj_id, tplc.crs_id FROM rbac_ua ua " . PHP_EOL
            . " JOIN rbac_fa fa ON ua.rol_id = fa.rol_id " . PHP_EOL
            . " JOIN object_reference obr ON fa.parent = obr.ref_id " . PHP_EOL
            . " JOIN object_data obd ON obr.obj_id = obd.obj_id " . PHP_EOL
            . " JOIN object_data obd2 ON (ua.rol_id = obd2.obj_id) " . PHP_EOL
            . " LEFT JOIN xcps_tpl_crs tplc ON tplc.crs_id = obd.obj_id" . PHP_EOL
            . " WHERE obd.type = 'crs'" . PHP_EOL
            . "     AND fa.assign = 'y' " . PHP_EOL
            . "     AND obr.deleted IS NULL" . PHP_EOL
            . "     AND ua.usr_id = " . $this->getDB()->quote($user_id, 'integer') . " " . PHP_EOL
            . "     AND obd2.title = " . $this->getDB()->concat(
                array(
                    array($this->getDB()->quote('il_crs_admin_', 'text')),
                    array('obr.ref_id'),
                ),
                false
            ) . PHP_EOL
            . " HAVING tplc.crs_id IS NULL";
        $res = $this->getDB()->query($query);
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $obj_ids[] = (int) $row["obj_id"];
        }

        return $obj_ids;
    }

    /**
     * Check user has needed permissions to view the course
     *
     * @param int 	$user_id
     * @param int 	$crs_ref_id
     * @param int 	$crs_id
     *
     * @return bool
     */
    protected function userHasAccess($user_id, $crs_ref_id, $crs_id)
    {
        require_once("Modules/Course/classes/class.ilObjCourseAccess.php");
        $visible = $this->access->checkAccessOfUser($user_id, "visible", "", $crs_ref_id);
        $read = $this->access->checkAccessOfUser($user_id, "read", "", $crs_ref_id);

        $always_visible = false;
        $active = \ilObjCourseAccess::_isActivated($crs_id, $always_visible);

        if ($visible && ($read && $active || (bool) $always_visible)) {
            return true;
        }

        return false;
    }

    /**
     * Check the course should be filtered
     *
     * @param \ilObjCourse 	$crs
     * @param string[] 	$filter
     *
     * @return bool
     */
    protected function catchedByFilter(\ilObjCourse $crs, $filter)
    {
        $catched = array();
        require_once(__DIR__ . "/class.ilAdministratedTrainingsGUI.php");
        if (isset($filter[\ilAdministratedTrainingsGUI::F_TYPE]) && $filter[\ilAdministratedTrainingsGUI::F_TYPE] != "") {
            $catched[] = $this->checkCourseType($crs->getRefId(), $filter[\ilAdministratedTrainingsGUI::F_TYPE]);
        }

        if (isset($filter[\ilAdministratedTrainingsGUI::F_MONTH]) && $filter[\ilAdministratedTrainingsGUI::F_MONTH] != "") {
            $catched[] = $this->checkCourseDates($crs, $filter[\ilAdministratedTrainingsGUI::F_MONTH]);
        }

        $catched = array_filter($catched, function ($c) {
            return $c === true;
        });

        return count($catched) > 0;
    }

    /**
     * Check on course type
     *
     * @param int 	$crs_ref_id
     * @param string 	$type
     *
     * @return bool
     */
    protected function checkCourseType($crs_ref_id, $type)
    {
        $xccl = $this->getFirstChildOfByType($crs_ref_id, "xccl");

        if ($xccl === null) {
            return true;
        }

        return $xccl->getCourseClassification()->getType() != $type;
    }

    /**
     * Checks the course dates are within the filter
     *
     * @param \ilObjCourse 	$crs
     * @param string 	$month
     *
     * @return bool
     */
    protected function checkCourseDates(\ilObjCourse $crs, $filter_date)
    {
        $crs_start = $crs->getCourseStart();
        if ($crs_start === null) {
            return false;
        }

        $crs_end = $crs->getCourseEnd();
        $last_day = $filter_date;
        $first_day = substr($last_day, 0, -2) . '01';

        if ($crs_start->get(IL_CAL_DATE) <= $last_day && $crs_end->get(IL_CAL_DATE) >= $first_day) {
            return false;
        }

        return true;
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
        $node_data = $this->tree->getNodeData($ref_id);
        $children = $this->tree->getSubtree($node_data, true, $search_type);

        if (count($children) === 0) {
            return null;
        }

        $child = array_shift($children);
        return \ilObjectFactory::getInstanceByRefId($child["child"]);
    }

    /**
     * Check the end date of course is passed
     *
     * @param \ilDateTime | null 	$end_date
     *
     * @return bool
     */
    protected function endDatePassed(\ilDateTime $end_date = null)
    {
        if ($end_date === null) {
            return true;
        }

        $today = date("Y-m-d");

        return $end_date->get(IL_CAL_DATE) < $today;
    }

    /**
     * Get initial ref_id of course
     *
     * @param int 	$crs_id
     *
     * @return int
     */
    protected function getRefId($crs_id)
    {
        $ref_ids = \ilObject::_getAllReferences($crs_id);
        sort($ref_ids);
        return array_shift($ref_ids);
    }

    /**
     * Creates an object for view
     *
     * @param int 	$ref_id
     * @param string 	$title
     * @param \ilDateTime | null 	$crs_start
     *
     * @return AdministratedTraining
     */
    protected function createAdministratedTraining($ref_id, $title, \ilDateTime $crs_start = null)
    {
        return new AdministratedTraining($ref_id, $title, $crs_start);
    }

    /**
     * Get intance of db
     *
     * @throws \Exceptio
     *
     * @return \ilDBInterface
     */
    protected function getDB()
    {
        if (!$this->db) {
            throw new \Exception("no Database");
        }
        return $this->db;
    }
}
