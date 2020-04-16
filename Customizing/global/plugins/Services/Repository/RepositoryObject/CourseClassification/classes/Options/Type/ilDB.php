<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\CourseClassification\Options\Type;

use CaT\Plugins\CourseClassification\Options\ilDB as OptionDB;

class ilDB extends OptionDB
{
    const TABLE_NAME = "xccl_type";
    const TABLE_SETTINGS = "xccl_data";

    /**
     * Get all CC-Objects where type option is selected
     *
     * @param int 	$id
     *
     * @return int[]
     */
    public function getAffectedCCObjectObjIds(int $id) : array
    {
        $ret = array();
        $query = 'SELECT obj_id' . PHP_EOL
            . ' FROM ' . self::TABLE_SETTINGS . PHP_EOL
            . ' WHERE type = ' . $this->getDB()->quote($id, 'integer')
        ;

        $res = $this->getDB()->query($query);
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $ret[] = (int) $row["obj_id"];
        }

        return $ret;
    }
}
