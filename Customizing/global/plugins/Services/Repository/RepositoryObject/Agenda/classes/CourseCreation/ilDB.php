<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */
/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\Agenda\CourseCreation;

class ilDB
{
    const TABLE_EVENTS = "event_items";

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @param int $ref_id
     * @return array
     * @throws \Exception
     */
    public function getAssignedSessions(int $ref_id) : array
    {
        $query = "SELECT event_id"
                . " FROM " . self::TABLE_EVENTS
                . " WHERE item_id = " . $this->getDB()->quote($ref_id, "integer");

        $res = $this->getDB()->query($query);
        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $ret[] = \ilObjectFactory::getInstanceByObjId((int) $row["event_id"]);
        }

        return $ret;
    }

    /**
     * @return \ilDBInterface
     * @throws \Exception
     */
    protected function getDB() : \ilDBInterface
    {
        if (!$this->db) {
            throw new \Exception("no Database");
        }
        return $this->db;
    }
}
