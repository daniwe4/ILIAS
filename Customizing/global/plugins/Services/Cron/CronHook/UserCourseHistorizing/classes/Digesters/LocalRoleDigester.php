<?php

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use CaT\Plugins\UserCourseHistorizing\HistCases\HistUserCourse;
use CaT\Historization\Digester\Digester as Digester;

class LocalRoleDigester implements Digester
{
    private $event;

    public function __construct($event)
    {
        assert('is_string($event)');
        $this->event = $event;
    }

    public function digest(array $payload)
    {
        $usr_id = $payload['usr_id'];
        $crs_id = $payload['obj_id'];
        $return = [];
        switch ($this->event) {
            case 'assignUser':
            case 'historizeLocalRoles':
                $return['roles'] = $this->getAllAssignedRoles((int) $crs_id, (int) $usr_id);
                break;
            case 'deassignUser':
                $return['roles'] = [];
                break;
        }
        return $return;
    }

    protected function getAllAssignedRoles(int $crs_id, int $usr_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ref_id = array_shift(\ilObject::_getAllReferences($crs_id));
        $query = "SELECT REPLACE(object_data.title, '_" . $ref_id . "', '') AS r_title"
            . " FROM rbac_fa"
            . " JOIN object_data"
            . "     ON object_data.obj_id = rbac_fa.rol_id"
            . " JOIN rbac_ua"
            . "     ON rbac_ua.rol_id = rbac_fa.rol_id"
            . " WHERE rbac_fa.parent = " . $ref_id
            . "     AND rbac_fa.assign = 'y'"
            . "     AND rbac_ua.usr_id = " . $usr_id
        ;

        $res = $ilDB->query($query);
        $ret = [];
        while ($row = $ilDB->fetchAssoc($res)) {
            $ret[] = $row["r_title"];
        }
        return $ret;
    }
}
