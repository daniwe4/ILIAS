<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\Agenda\Config\Blocks;

class ilDB implements DB
{
    const TABLE_NAME = "xage_config_block";

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

    public function saveBlockConfig(bool $edit_fixed_blocks)
    {
        $id = $this->nextId();
        $values = [
            "id" => [
                "integer",
                $id
            ],
            "edit_fixed_blocks" => [
                "integer",
                $edit_fixed_blocks
            ],
            "changed_by" => [
                "integer",
                (int) $this->usr->getId()
            ],
            "changed_date" => [
                "text",
                date("Y-m-d H:i:s")
            ]
        ];
        $this->db->insert(self::TABLE_NAME, $values);
    }

    public function selectBlockConfig() : Block
    {
        $query = "SELECT edit_fixed_blocks" . PHP_EOL
            . "FROM " . self::TABLE_NAME . PHP_EOL
            . "ORDER BY id DESC" . PHP_EOL
            . "LIMIT 1"
        ;

        $res = $this->db->query($query);

        if ($this->db->numRows($res) == 0) {
            return new Block(false);
        }

        $row = $this->db->fetchAssoc($res);
        return new Block(
            (bool) $row["edit_fixed_blocks"]
        );
    }

    public function createTable()
    {
        if (!$this->db->tableExists(self::TABLE_NAME)) {
            $fields = [
                "id" => [
                    "type" => "integer",
                    "length" => 4,
                    "notnull" => true
                ],
                "edit_fixed_blocks" => [
                    "type" => "integer",
                    "length" => 1,
                    "notnull" => true
                ],
                "changed_by" => [
                    "type" => "integer",
                    "length" => 4,
                    "notnull" => true
                ],
                "changed_date" => [
                    "type" => "text",
                    "length" => 22,
                    "notnull" => true
                ]
            ];

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

    protected function nextId() : int
    {
        return (int) $this->db->nextId(self::TABLE_NAME);
    }
}
