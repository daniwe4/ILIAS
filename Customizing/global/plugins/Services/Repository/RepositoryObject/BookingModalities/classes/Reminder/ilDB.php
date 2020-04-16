<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\BookingModalities\Reminder;

class ilDB implements DB
{
    const TABLE_NAME = "xbkm_minmem_reminder";
    /**
     * @var \ilDBInterface
     */
    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function insert(bool $send_mail, int $days_before_course, int $usr_id)
    {
        $next_id = $this->db->nextId(self::TABLE_NAME);
        $vals = [
            "id" => [
                "integer",
                $next_id
            ],
            "send_mail" => [
                "integer",
                $send_mail
            ],
            "days_before_course" => [
                "integer",
                $days_before_course
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

        $this->db->insert(self::TABLE_NAME, $vals);
    }
    public function select() : MinMember
    {
        $query = "SELECT send_mail, days_before_course FROM " . self::TABLE_NAME . " ORDER BY id DESC LIMIT 1";
        $res = $this->db->query($query);

        if ($this->db->numRows($res) == 0) {
            return new MinMember(false, 0);
        }

        $row = $this->db->fetchAssoc($res);
        return new MinMember((bool) $row["send_mail"], (int) $row["days_before_course"]);
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
                'send_mail' => [
                    'type' => 'integer',
                    'length' => 1,
                    'notnull' => true,
                    "default" => 0
                ],
                'days_before_course' => [
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
