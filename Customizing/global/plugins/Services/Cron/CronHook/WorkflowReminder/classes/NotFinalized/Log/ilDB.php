<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WorkflowReminder\NotFinalized\Log;

class ilDB implements DB
{
    const TABLE_NAME = "not_finalized_log";

    /**
     * @var \ilDBInterface
     */
    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function insert(int $crs_ref_id, int $child_ref_id, \DateTime $date)
    {
        $id = $this->db->nextId(self::TABLE_NAME);
        $values = [
            "id" => [
                "integer",
                $id
            ],
            "crs_ref_id" => [
                "integer",
                $crs_ref_id
            ],
            "child_ref_id" => [
                "integer",
                $child_ref_id
            ],
            "last_send" => [
                "text",
                $date->format("Y-m-d")
            ]
        ];

        $this->db->insert(self::TABLE_NAME, $values);
    }

    public function createLogTable()
    {
        if (!$this->db->tableExists(self::TABLE_NAME)) {
            $fields =
                array(
                    "id" => array(
                        "type" => "integer",
                        "length" => 4,
                        "notnull" => true
                    ),
                    "crs_ref_id" => array(
                        "type" => "integer",
                        "length" => 4,
                        "notnull" => true
                    ),
                    "child_ref_id" => array(
                        "type" => "integer",
                        "length" => 4,
                        "notnull" => true
                    ),
                    "last_send" => array(
                        "type" => "text",
                        "length" => 12,
                        "notnull" => true
                    )
                );

            $this->db->createTable(self::TABLE_NAME, $fields);
        }
    }

    public function createSequence()
    {
        if (!$this->db->tableExists(self::TABLE_NAME . "_seq")) {
            $this->db->createSequence(self::TABLE_NAME);
        }
    }

    public function createPrimaryKey()
    {
        try {
            $this->db->addPrimaryKey(self::TABLE_NAME, array("id"));
        } catch (\PDOException $e) {
            $this->db->dropPrimaryKey(self::TABLE_NAME);
            $this->db->addPrimaryKey(self::TABLE_NAME, array("id"));
        }
    }
}
