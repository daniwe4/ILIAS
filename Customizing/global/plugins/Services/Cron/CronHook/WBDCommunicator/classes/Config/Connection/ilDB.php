<?php

/* Copyright (c) 2019 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

declare(strict_types = 1);

namespace CaT\Plugins\WBDCommunicator\Config\Connection;

class ilDB implements DB
{
    const TABLE_NAME = "wbd_con_config";

    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var \ilObjUser
     */
    protected $usr;

    public function __construct(\ilDBInterface $db, \ilObjUser $usr)
    {
        $this->db = $db;
        $this->usr = $usr;
    }

    public function saveConnection(Connection $connection)
    {
        $id = $this->getNextId();

        $values = [
            "id" => [
                "integer",
                $id
            ],
            "host" => [
                "text",
                $connection->getHost()
            ],
            "port" => [
                "text",
                $connection->getPort()
            ],
            "endpoint" => [
                "text",
                $connection->getEndpoint()
            ],
            "namespace" => [
                "text",
                $connection->getNamespace()
            ],
            "name" => [
                "clob",
                $connection->getName()
            ],
            "changed_by" => [
                "integer",
                $this->usr->getId()
            ],
            "changed_at" => [
                "text",
                date("Y-m-d H:i:s")
            ]
        ];

        $this->db->insert(self::TABLE_NAME, $values);
    }

    public function getConnection() : Connection
    {
        $table = self::TABLE_NAME;

        $query = <<<SQL
SELECT host, port, endpoint, namespace, name
FROM $table
ORDER BY id DESC
LIMIT 1
SQL;

        $res = $this->db->query($query);
        if ($this->db->numRows($res) == 0) {
            return new Connection(
                "",
                "",
                "",
                "",
                ""
            );
        }

        $row = $this->db->fetchAssoc($res);
        return new Connection(
            $row["host"],
            $row["port"],
            $row["endpoint"],
            $row["namespace"],
            $row["name"]
        );
    }

    public function createTable()
    {
        if (!$this->db->tableExists("wbd_con_config")) {
            $fields = [
                "id" => [
                    "type" => "integer",
                    "length" => 4,
                    "notnull" => true
                ],
                "host" => [
                    "type" => "clob",
                    "notnull" => true
                ],
                "port" => [
                    "type" => "text",
                    "length" => 10,
                    "notnull" => true
                ],
                "endpoint" => [
                    "type" => "clob",
                    "notnull" => true
                ],
                "namespace" => [
                    "type" => "clob",
                    "notnull" => true
                ],
                "name" => [
                    "type" => "clob",
                    "notnull" => true
                ],
                "changed_by" => [
                    "type" => "integer",
                    "length" => 4,
                    "notnull" => true
                ],
                "changed_at" => [
                    "type" => "text",
                    "length" => 21,
                    "notnull" => true
                ]
            ];

            $this->db->createTable("wbd_con_config", $fields);
        }
    }

    public function createPrimaryKey()
    {
        if (!$this->db->tableExists("wbd_con_config" . "_seq")) {
            $this->db->createSequence("wbd_con_config");
        }
    }

    public function createSequence()
    {
        try {
            $this->db->addPrimaryKey("wbd_con_config", ["id"]);
        } catch (\PDOException $e) {
            $this->db->dropPrimaryKey("wbd_con_config");
            $this->db->addPrimaryKey("wbd_con_config", ["id"]);
        }
    }

    protected function getNextId() : int
    {
        return (int) $this->db->nextId(self::TABLE_NAME);
    }
}
