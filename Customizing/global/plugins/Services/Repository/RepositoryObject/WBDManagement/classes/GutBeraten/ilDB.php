<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WBDManagement\GutBeraten;

class ilDB implements DB
{
    const TABLE_NAME = "xwbm_usr_udf_vals";

    /**
     * @var
     */
    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function saveWBDData(int $usr_id, string $wbd_id, string $status)
    {
        $values = [
            "usr_id" => [
                "integer",
                $usr_id
            ],
            "wbd_id" => [
                "text",
                $wbd_id
            ],
            "status" => [
                "text",
                $status
            ],
            "approve_date" => [
                "text",
                date("Y-m-d H:i:s")
            ]
        ];

        $this->db->insert(self::TABLE_NAME, $values);
    }

    public function deleteWBDDataFor(int $usr_id)
    {
        $q = 'DELETE FROM ' . self::TABLE_NAME . PHP_EOL
            . ' WHERE usr_id = ' . $this->db->quote($usr_id, 'integer');

        $this->db->manipulate($q);
    }

    public function selectFor(int $usr_id)
    {
        $query = "SELECT usr_id, wbd_id, status, approve_date" . PHP_EOL
            . "FROM " . self::TABLE_NAME . PHP_EOL
            . "WHERE usr_id = " . $this->db->quote($usr_id, "integer");

        $res = $this->db->query($query);

        if ($this->db->numRows($res) == 0) {
            return null;
        }

        $row = $this->db->fetchAssoc($res);

        return new WBDData(
            (int) $row["usr_id"],
            $row["wbd_id"],
            $row["status"],
            \DateTime::createFromFormat("Y-m-d H:i:s", $row["approve_date"])
        );
    }

    public function createTable()
    {
        if (!$this->db->tableExists(self::TABLE_NAME)) {
            $fields = [
                'usr_id' => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ],
                'wbd_id' => [
                    'type' => 'text',
                    'length' => 18,
                    'notnull' => true
                ],
                'status' => [
                    'type' => 'text',
                    'length' => 40,
                    'notnull' => true
                ],
                'approve_date' => [
                    'type' => 'text',
                    'length' => 21,
                    'notnull' => true
                ]
            ];

            $this->db->createTable(self::TABLE_NAME, $fields);
        }
    }

    public function createPrimaryKey()
    {
        try {
            $this->db->addPrimaryKey(self::TABLE_NAME, ["usr_id"]);
        } catch (\PDOException $e) {
            $this->db->dropPrimaryKey(self::TABLE_NAME);
            $this->db->addPrimaryKey(self::TABLE_NAME, ["usr_id"]);
        }
    }
}
