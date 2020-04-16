<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */
/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\Agenda\AgendaEntry;

use DateTimeZone;
use DateTime;

class ilDB implements DB
{
    const TABLE_NAME = "xage_entries";
    const TABLE_SETTINGS = "xage_settings";

    /**
     * @var \ilDBInterface
     */
    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function create(
        int $obj_id,
        int $pool_item_id,
        int $duration,
        int $position,
        bool $is_blank = false,
        string $agenda_item_content = null,
        string $goals = null
    ) : AgendaEntry {
        $next_id = $this->getNextId();
        $entry = new AgendaEntry(
            $next_id,
            $obj_id,
            $pool_item_id,
            $duration,
            $position,
            0.0,
            $is_blank,
            $agenda_item_content,
            $goals
        );

        $values = array(
            "id" => array("integer", $entry->getId()),
            "obj_id" => array("integer", $entry->getObjId()),
            "pool_item_id" => array("integer", $entry->getPoolItemId()),
            "duration" => array("integer", $entry->getDuration()),
            "position" => array("integer", $entry->getPosition()),
            "is_blank" => array("integer", $entry->getIsBlank()),
            "agenda_item_content" => array("text", $entry->getAgendaItemContent()),
            "goals" => array("text", $entry->getGoals())
        );

        $this->getDB()->insert(self::TABLE_NAME, $values);

        return $entry;
    }

    /**
     * @inheritdoc
     */
    public function update(AgendaEntry $entry)
    {
        $where = array(
            "id" => array("integer", $entry->getId())
        );

        $values = array(
            "pool_item_id" => array("integer", $entry->getPoolItemId()),
            "duration" => array("integer", $entry->getDuration()),
            "position" => array("integer", $entry->getPosition()),
            "is_blank" => array("integer", $entry->getIsBlank()),
            "agenda_item_content" => array("text", $entry->getAgendaItemContent()),
            "goals" => array("text", $entry->getGoals())
        );

        $this->getDB()->update(self::TABLE_NAME, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function delete(int $id)
    {
        $query = "DELETE FROM " . self::TABLE_NAME . PHP_EOL
                . " WHERE id = " . $this->getDB()->quote($id, "integer");

        $this->getDB()->manipulate($query);
    }

    /**
     * @inheritdoc
     */
    public function deleteFor(int $obj_id)
    {
        $query = "DELETE FROM " . self::TABLE_NAME . PHP_EOL
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");

        $this->getDB()->manipulate($query);
    }

    /**
     * @inheritdoc
     */
    public function selectFor(int $obj_id) : array
    {
        $query = "SELECT id, obj_id, pool_item_id, duration, position," . PHP_EOL
                . "     is_blank, agenda_item_content, goals" . PHP_EOL
                . " FROM " . self::TABLE_NAME . PHP_EOL
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer") . PHP_EOL
                . "ORDER BY position" . PHP_EOL
        ;

        $res = $this->getDB()->query($query);
        $ret = array();

        while ($row = $this->getDB()->fetchAssoc($res)) {
            $item = new AgendaEntry(
                (int) $row["id"],
                (int) $row["obj_id"],
                (int) $row["pool_item_id"],
                (int) $row["duration"],
                (int) $row["position"],
                (float) $row["duration"],
                (bool) $row["is_blank"],
                $row["agenda_item_content"],
                $row["goals"]
            );

            $ret[] = $item;
        }

        return $ret;
    }

    protected function fillUp(string $value)
    {
        return str_pad($value, 2, "0", STR_PAD_LEFT);
    }

    public function selectForId(int $id) : AgendaEntry
    {
        $query = "SELECT id, obj_id, pool_item_id, duration, position," . PHP_EOL
            . "     is_blank, agenda_item_content, goals" . PHP_EOL
            . " FROM " . self::TABLE_NAME . PHP_EOL
            . " WHERE id = " . $this->getDB()->quote($id, "integer");

        $res = $this->getDB()->query($query);
        $row = $this->getDB()->fetchAssoc($res);

        $duration = (int) $row["duration"];
        $idd_time = $duration;

        return new AgendaEntry(
            (int) $row["id"],
            (int) $row["obj_id"],
            (int) $row["pool_item_id"],
            $duration,
            (int) $row["position"],
            (float) $idd_time,
            (bool) $row["is_blank"],
            $row["agenda_item_content"],
            $row["goals"]
        );
    }

    public function getDayStartAndEnd(int $obj_id) : array
    {
        $sql =
              "SELECT sum(ae.duration) as duration, s.start_time" . PHP_EOL
             . "FROM " . self::TABLE_SETTINGS . " s" . PHP_EOL
             . "LEFT JOIN " . self::TABLE_NAME . " ae" . PHP_EOL
             . "ON s.obj_id = ae.obj_id" . PHP_EOL
             . "WHERE s.obj_id = " . $this->getDB()->quote($obj_id, "integer") . PHP_EOL
        ;

        $result = $this->getDB()->query($sql);
        $row = $this->getDB()->fetchAssoc($result);


        $start = $row["start_time"];
        $start_date = DateTime::createFromFormat("H:i", $start, new DateTimeZone("Europe/Berlin"));
        if (!is_null($row["duration"])) {
            $start_date->modify("+" . $row["duration"] . " minutes");
        }
        $end = $start_date->format("H:i");

        return ["start" => $start . ":00", "end" => $end . ":00"];
    }

    public function getNewEntry(int $obj_id) : AgendaEntry
    {
        return new AgendaEntry(-1, $obj_id);
    }

    public function createTable()
    {
        if (!$this->getDB()->tableExists(self::TABLE_NAME)) {
            $fields = array(
                'id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'obj_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'pool_item_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'start_time' => array(
                    'type' => 'text',
                    'length' => 19,
                    'notnull' => true
                ),
                'end_time' => array(
                    'type' => 'text',
                    'length' => 19,
                    'notnull' => true
                )
            );
            $this->getDB()->createTable(self::TABLE_NAME, $fields);
        }
    }

    public function createPrimaryKey()
    {
        $this->getDB()->addPrimaryKey(self::TABLE_NAME, array('id'));
    }

    public function createSequence()
    {
        $this->getDB()->createSequence(self::TABLE_NAME);
    }

    /**
     * update step1 for db
     *
     * @return void
     */
    public function update1()
    {
        if ($this->getDB()->tableColumnExists(self::TABLE_NAME, "start_time")) {
            $field = array(
                'type' => 'text',
                'length' => 19,
                'notnull' => false
            );

            $this->getDB()->modifyTableColumn(self::TABLE_NAME, "start_time", $field);
        }
        if ($this->getDB()->tableColumnExists(self::TABLE_NAME, "end_time")) {
            $field = array(
                'type' => 'text',
                'length' => 19,
                'notnull' => false
            );

            $this->getDB()->modifyTableColumn(self::TABLE_NAME, "end_time", $field);
        }
    }

    public function update2()
    {
        if ($this->getDB()->tableColumnExists(self::TABLE_NAME, "start_time")) {
            $field = array(
                'type' => 'text',
                'length' => 10,
                'notnull' => true,
                'default' => "00:00:00"
            );

            $this->getDB()->modifyTableColumn(self::TABLE_NAME, "start_time", $field);
        }

        if ($this->getDB()->tableColumnExists(self::TABLE_NAME, "end_time")) {
            $field = array(
                'type' => 'text',
                'length' => 10,
                'notnull' => true,
                'default' => "00:00:00"
            );

            $this->getDB()->modifyTableColumn(self::TABLE_NAME, "end_time", $field);
        }
    }

    public function update3()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "is_blank")) {
            $field = array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => false
            );

            $this->getDB()->addTableColumn(self::TABLE_NAME, "is_blank", $field);
        }

        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "agenda_item_content")) {
            $field = array(
                'type' => 'clob',
                'notnull' => false
            );

            $this->getDB()->addTableColumn(self::TABLE_NAME, "agenda_item_content", $field);
        }

        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "goals")) {
            $field = array(
                'type' => 'clob',
                'notnull' => false
            );

            $this->getDB()->addTableColumn(self::TABLE_NAME, "goals", $field);
        }
    }

    public function update4()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "duration")) {
            $field = [
                'type' => 'integer',
                'length' => 1,
                'notnull' => false
            ];
            $this->getDB()->addTableColumn(self::TABLE_NAME, "duration", $field);
        }

        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "position")) {
            $field = [
                'type' => 'integer',
                'length' => 1,
                'notnull' => false
            ];
            $this->getDB()->addTableColumn(self::TABLE_NAME, "position", $field);
        }
    }

    public function update5()
    {
        $sql =
            "SELECT id, obj_id, start_time, end_time" . PHP_EOL
            . "FROM " . self::TABLE_NAME . PHP_EOL
            . "ORDER BY obj_id, start_time" . PHP_EOL;

        $result = $this->getDB()->query($sql);

        $obj_id = 0;
        $pos = 0;

        while ($row = $this->getDB()->fetchAssoc($result)) {
            if ($obj_id != $row["obj_id"]) {
                $obj_id = $row["obj_id"];
                $pos = 10;
            }

            $start = new DateTime($row["start_time"], new DateTimeZone("Europe/Berlin"));
            $end = new DateTime($row["end_time"], new DateTimeZone("Europe/Berlin"));

            $diff_h = $start->diff($end)->h;
            $diff_i = $start->diff($end)->i;
            $diff = $diff_h * 60 + $diff_i;

            $sql =
                "UPDATE " . self::TABLE_NAME . PHP_EOL
                . "SET duration = " . $diff . "," . PHP_EOL
                . "    position = " . $pos . PHP_EOL
                . "WHERE id = " . $row["id"] . PHP_EOL;

            $this->getDB()->manipulate($sql);
            $pos = $pos + 10;
        }
    }

    public function update6()
    {
        if ($this->getDB()->tableColumnExists(self::TABLE_NAME, "start_time")) {
            $this->getDB()->dropTableColumn(self::TABLE_NAME, "start_time");
        }

        if ($this->getDB()->tableColumnExists(self::TABLE_NAME, "end_time")) {
            $this->getDB()->dropTableColumn(self::TABLE_NAME, "end_time");
        }
    }

    public function update7()
    {
        if ($this->getDB()->tableColumnExists(self::TABLE_NAME, "duration")) {
            $field = [
                'type' => 'integer',
                'length' => 4,
                'notnull' => false
            ];
            $this->getDB()->modifyTableColumn(self::TABLE_NAME, "duration", $field);
        }
    }

    public function update8()
    {
        if ($this->getDB()->tableColumnExists(self::TABLE_NAME, "position")) {
            $field = [
                'type' => 'integer',
                'length' => 4,
                'notnull' => false
            ];
            $this->getDB()->modifyTableColumn(self::TABLE_NAME, "position", $field);
        }
    }


    /**
     * @throws \Exception if no db is set
     * @return \ilDBInterface
     */
    protected function getDB() : \ilDBInterface
    {
        if (!$this->db) {
            throw new \Exception("no Database");
        }
        return $this->db;
    }

    protected function getNextId() : int
    {
        return (int) $this->getDB()->nextId(self::TABLE_NAME);
    }
}
