<?php

/* Copyright (c) 2019 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

declare(strict_types = 1);

namespace CaT\Plugins\WBDCommunicator\Config\Tgic;

class ilDB implements DB
{
    const TABLE_NAME = "wbd_tgic_config";

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

    /**
     * @inheritDoc
     */
    public function saveTgicSettings(
        string $partner_id,
        string $certstore,
        string $password
    ) : Tgic {
        $tgic = new Tgic(
            $partner_id,
            $certstore,
            $password
        );

        $id = $this->db->nextId(self::TABLE_NAME);
        $values = [
            "id" => [
                "integer",
                $id
            ],
            "partner_id" => [
                "text",
                $partner_id
            ],
            "certstore" => [
                "text",
                $certstore
            ],
            "password" => [
                "text",
                $password
            ],
            "changed_by" => [
                "integer",
                $this->usr->getId()
            ],
            "changed_date" => [
                "text",
                date("Y-m-d")
            ]
        ];

        $this->db->insert(self::TABLE_NAME, $values);

        return $tgic;
    }

    /**
     * @inheritDoc
     */
    public function getTgicSettings() : Tgic
    {
        $table = self::TABLE_NAME;
        $query = <<<SQL
SELECT partner_id, certstore, password
FROM $table
ORDER BY id DESC
LIMIT 1
SQL;

        $res = $this->db->query($query);

        if ($this->db->numRows($res) == 0) {
            throw new \LogicException("No configuration for tgic found");
        }

        $row = $this->db->fetchAssoc($res);
        return new Tgic(
            $row["partner_id"],
            $row["certstore"],
            $row["password"]
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
                "partner_id" => [
                    "type" => "text",
                    "length" => 100,
                    "notnull" => true
                ],
                "certstore" => [
                    "type" => "clob",
                    "notnull" => true
                ],
                "password" => [
                    "type" => "text",
                    "length" => 100,
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

    public function createPrimaryKey()
    {
        if (!$this->db->tableExists(self::TABLE_NAME . "_seq")) {
            $this->db->createSequence(self::TABLE_NAME);
        }
    }

    public function createSequence()
    {
        try {
            $this->db->addPrimaryKey(self::TABLE_NAME, ["id"]);
        } catch (\PDOException $e) {
            $this->db->dropPrimaryKey(self::TABLE_NAME);
            $this->db->addPrimaryKey(self::TABLE_NAME, ["id"]);
        }
    }
}
