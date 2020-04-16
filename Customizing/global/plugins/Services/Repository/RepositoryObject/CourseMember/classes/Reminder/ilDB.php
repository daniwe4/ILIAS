<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\CourseMember\Reminder;

class ilDB implements DB
{
    const TABLE_NAME = "xcmb_reminder_settings";
    /**
     * @var \ilDBInterface
     */
    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function insert(int $interval, int $usr_id)
    {
        $next_id = $this->db->nextId(self::TABLE_NAME);
        $values = [
            "id" => [
                "integer",
                $next_id
            ],
            "check_interval" => [
                "integer",
                $interval
            ],
            "edit_by" => [
                "integer",
                $usr_id
            ],
            "last_edit" => [
                "text",
                date("Y-m-d H:i:s")
            ]
        ];

        $this->db->insert(self::TABLE_NAME, $values);
    }

    public function select() : NotFinalized
    {
        $query = <<<EOT
SELECT check_interval
FROM xcmb_reminder_settings
ORDER BY id DESC
LIMIT 1
EOT;

        $res = $this->db->query($query);

        if ($this->db->numRows($res) == 0) {
            return new NotFinalized(0);
        }
        $row = $this->db->fetchAssoc($res);
        return new NotFinalized((int) $row["check_interval"]);
    }

    public function createTable()
    {
        if (!$this->db->tableExists(self::TABLE_NAME)) {
            $fields = [
                "id" => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ],
                'check_interval' => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => false
                ],
                "edit_by" => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ],
                "last_edit" => [
                    "type" => "text",
                    "length" => 20,
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
}
