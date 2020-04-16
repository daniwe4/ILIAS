<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\BookingAcknowledge\Acknowledgments;

/**
 * ILIAS implementation of storage for Acknowledgments
 */
class ilDB implements DB
{
    const TABLE_NAME = "xack_requests";
    const DATE_FORMAT = "d.m.Y H:i";

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function create(
        int $acting_usr_id,
        int $usr_id,
        int $crs_ref_id,
        int $state
    ) : Acknowledgment {
        $next_id = $this->getNextId(self::TABLE_NAME);
        $now = new \DateTime('now');
        $ack = new Acknowledgment(
            $next_id,
            $acting_usr_id,
            $usr_id,
            $crs_ref_id,
            $now,
            $state
        );

        $dat = date_format($ack->getLastUpdateDate(), self::DATE_FORMAT);
        $values = array(
            "id" => array("integer", $ack->getId()),
            "acting_usr_id" => array("integer", $ack->getActingUserId()),
            "usr_id" => array("integer", $ack->getUserId()),
            "crs_ref_id" => array("integer", $ack->getCourseRefId()),
            "dat" => array("text", $dat),
            "state" => array("integer", $ack->getState())
        );

        $this->getDB()->insert(self::TABLE_NAME, $values);
        return $ack;
    }

    protected function createAcknowledgmentObject(array $row) : Acknowledgment
    {
        return new Acknowledgment(
            (int) $row["id"],
            (int) $row["acting_usr_id"],
            (int) $row["usr_id"],
            (int) $row["crs_ref_id"],
            new \DateTime($row["dat"]),
            (int) $row["state"]
        );
    }

    protected function getSelectQuery(string $where) : string
    {
        $query =
             "SELECT id, acting_usr_id, usr_id, crs_ref_id, dat, state" . PHP_EOL
            . " FROM " . self::TABLE_NAME . PHP_EOL
            . $where;

        return  $query;
    }

    /**
     * @throws \Exception if no db is set
     */
    protected function getDB() : \ilDBInterface
    {
        if (!$this->db) {
            throw new \Exception("no database");
        }
        return $this->db;
    }

    protected function getNextId(string $table_name) : int
    {
        return (int) $this->getDB()->nextId($table_name);
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
                'usr_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'crs_ref_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'dat' => array(
                    'type' => 'text',
                    'length' => 19,
                    'notnull' => true
                ),
                'state' => array(
                    'type' => 'integer',
                    'length' => 2,
                    'notnull' => true
                )
            );
            $this->getDB()->createTable(self::TABLE_NAME, $fields);
        }
    }

    public function createPrimaryKey()
    {
        try {
            $this->getDB()->addPrimaryKey(self::TABLE_NAME, array('id', 'usr_id', 'crs_ref_id'));
        } catch (\PDOException $e) {
            $this->getDB()->dropPrimaryKey(self::TABLE_NAME);
            $this->getDB()->addPrimaryKey(self::TABLE_NAME, array('id', 'usr_id', 'crs_ref_id'));
        }
    }

    public function createSequenceRequests()
    {
        if (!$this->getDB()->tableExists(self::TABLE_NAME . '_seq')) {
            $this->getDB()->createSequence(self::TABLE_NAME);
        }
    }

    public function updateTable1()
    {
        $field = array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        );

        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "acting_usr_id")) {
            $this->getDB()->addTableColumn(self::TABLE_NAME, "acting_usr_id", $field);
        }
    }
}
