<?php

namespace CaT\Plugins\UserCourseHistorizing;

/**
 * re-historize booking/storno dates
 */
class RehistorizeBookingModalities
{
    public function __construct(\ilDBInterface $db, \ilTree $tree, \ilObjectDefinition $obj_def)
    {
        $this->db = $db;
        $this->tree = $tree;
        $this->obj_def = $obj_def;
    }

    public function run()
    {
        $crss = $this->getFutureCourses();
        foreach ($crss as $ref_id) {
            $bkm = $this->getFirstChildOfByType($ref_id, 'xbkm');
            if (!is_null($bkm)) {
                $bkm->getActions()->raiseUpdateEvent();
            }
        }
    }

    /**
     * Get all future courses from history
     *
     * @return int[]
     */
    protected function getFutureCourses()
    {
        $query = "SELECT hhd.crs_id, oref.ref_id " . PHP_EOL
                . " FROM hhd_crs hhd" . PHP_EOL
                . " JOIN object_reference oref ON oref.obj_id = hhd.crs_id" . PHP_EOL
                . " WHERE hhd.begin_date > NOW()";
        $res = $this->db->query($query);

        $ret = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $ret[] = (int) $row["ref_id"];
        }
        return $ret;
    }

    protected function getFirstChildOfByType(int $ref_id, string $search_type)
    {
        require_once("Services/Object/classes/class.ilObjectFactory.php");

        $childs = $this->tree->getChilds($ref_id);
        foreach ($childs as $child) {
            $type = $child["type"];
            if ($type == $search_type) {
                return \ilObjectFactory::getInstanceByRefId($child["child"]);
            }

            if ($this->obj_def->isContainer($type)) {
                $ret = $this->getFirstChildOfByType($child["child"], $search_type);
                if (!is_null($ret)) {
                    return $ret;
                }
            }
        }

        return null;
    }
}
