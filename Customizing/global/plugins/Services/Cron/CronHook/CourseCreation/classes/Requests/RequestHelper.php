<?php

namespace CaT\Plugins\CourseCreation\Requests;

trait RequestHelper
{
    protected function getTitleByRefId($crs_ref_id)
    {
        $obj_id = \ilObject::_lookupObjId($crs_ref_id);
        return \ilObject::_lookupTitle($obj_id);
    }

    protected function isDeleted($crs_ref_id)
    {
        $query = "SELECT obj_id, deleted" . PHP_EOL
                . " FROM object_reference " . PHP_EOL
                . " WHERE ref_id = " . $this->getDB()->quote($crs_ref_id, "integer");

        $res = $this->getDB()->query($query);

        if ($this->getDB()->numRows($res) == 0) {
            return true;
        }

        $row = $this->getDB()->fetchAssoc($res);

        return $row["deleted"] != null;
    }

    protected function getCourse($crs_ref_id)
    {
        return \ilObjectFactory::getInstanceByRefId($crs_ref_id);
    }

    protected function getDB()
    {
        if ($this->db === null) {
            global $DIC;
            $this->db = $DIC->database();
        }

        return $this->db;
    }
}
