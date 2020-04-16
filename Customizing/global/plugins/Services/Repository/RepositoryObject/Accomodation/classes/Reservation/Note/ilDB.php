<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\Accomodation\Reservation\Note;

use Exception;
use ilDBInterface;

class ilDB implements DB
{
    const TABLE_NAME = "xoac_note";

    /**
     * @var ilDBInterface
     */
    protected $db;

    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function createNote(int $oac_obj_id, int $usr_id, string $note) : Note
    {
        $note = $this->buildNote($oac_obj_id, $usr_id, $note);

        $values = [
            "oac_obj_id" => ["int", $note->getOacObjId()],
            "usr_id" => ["int", $note->getUsrId()],
            "note" => ["text", $note->getNote()]
        ];

        $this->getDB()->insert(self::TABLE_NAME, $values);

        return $note;
    }

    public function update(int $oac_obj_id, int $usr_id, string $note)
    {
        $note = $this->buildNote($oac_obj_id, $usr_id, $note);

        $where = [
            "oac_obj_id" => ["int", $note->getOacObjId()],
            "usr_id" => ["int", $note->getUsrId()]
        ];

        $values = [
            "note" => ["text", $note->getNote()]
        ];

        $this->getDB()->update(self::TABLE_NAME, $values, $where);
    }

    public function delete(int $oac_obj_id, int $usr_id)
    {
        $sql =
             "DELETE FROM " . self::TABLE_NAME . PHP_EOL
            . "WHERE oac_obj_id = " . $this->getDB()->quote($oac_obj_id, "int") . PHP_EOL
            . "AND usr_id = " . $this->getDB()->quote($usr_id, "int") . PHP_EOL
        ;

        $this->getDB()->manipulate($sql);
    }

    public function deleteAllForUser(int $usr_id)
    {
        $sql =
            "DELETE FROM " . self::TABLE_NAME . PHP_EOL
            . "WHERE usr_id = " . $this->getDB()->quote($usr_id, "int") . PHP_EOL
        ;

        $this->getDB()->manipulate($sql);
    }

    public function nodeExists(int $oac_obj_id, int $usr_id) : bool
    {
        $sql =
             "SELECT oac_obj_id, usr_id, note" . PHP_EOL
            . "FROM " . self::TABLE_NAME . PHP_EOL
            . "WHERE oac_obj_id = " . $this->getDB()->quote($oac_obj_id, "integer") . PHP_EOL
            . "AND usr_id = " . $this->getDB()->quote($usr_id, "integer") . PHP_EOL
        ;

        $result = $this->getDB()->query($sql);

        return $this->getDB()->numRows($result) > 0;
    }

    public function selectNoteFor(int $oac_obj_id, int $usr_id)
    {
        $sql =
            "SELECT oac_obj_id, usr_id, note" . PHP_EOL
            . "FROM " . self::TABLE_NAME . PHP_EOL
            . "WHERE oac_obj_id = " . $this->getDB()->quote($oac_obj_id, "integer") . PHP_EOL
            . "AND usr_id = " . $this->getDB()->quote($usr_id, "integer") . PHP_EOL
        ;

        $result = $this->getDB()->query($sql);

        if ($this->getDB()->numRows($result) == 0) {
            return null;
        }

        $row = $this->getDB()->fetchAssoc($result);

        return $this->buildNote(
            (int) $row["oac_obj_id"],
            (int) $row["usr_id"],
            $row["note"]
        );
    }

    protected function buildNote(int $oac_obj_id, int $usr_id, string $note) : Note
    {
        return new Note($oac_obj_id, $usr_id, $note);
    }

    protected function getDB() : ilDBInterface
    {
        if (!$this->db) {
            throw new Exception("no Database");
        }
        return $this->db;
    }

    public function createTable()
    {
        if (!$this->getDB()->tableExists(self::TABLE_NAME)) {
            $fields = [
                "oac_obj_id" => [
                    "type" => "integer",
                    "length" => 4,
                    "notnull" => true
                ],
                "usr_id" => [
                    "type" => "integer",
                    "length" => 4,
                    "notnull" => true
                ],
                "note" => [
                    "type" => "clob",
                    "notnull" => false
                ]
            ];

            $this->getDB()->createTable(self::TABLE_NAME, $fields);
        }
    }

    public function createPrimaryKey()
    {
        $this->getDB()->addPrimaryKey(static::TABLE_NAME, ["oac_obj_id", "usr_id"]);
    }
}
