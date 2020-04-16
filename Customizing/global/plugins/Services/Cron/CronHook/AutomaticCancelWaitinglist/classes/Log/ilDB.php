<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\AutomaticCancelWaitinglist\Log;

class ilDB implements DB
{
    const LOG_TABLE_NAME = "acwaiting_log";
    const SUCCESS = "success";
    const FAIL = "fail";

    /**
     * @var \ilDBInterface
     */
    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }
    public function logSuccess(int $crs_ref_id, string $today)
    {
        $next_id = $this->db->nextId(self::LOG_TABLE_NAME);
        $vals = [
            "id" => ["integer", $next_id],
            "crs_ref_id" => ["integer", $crs_ref_id],
            "date" => ["text", $today],
            "state" => ["text", self::SUCCESS]
        ];

        $this->db->insert(self::LOG_TABLE_NAME, $vals);
    }

    public function logFail(int $crs_ref_id, string $today, string $message)
    {
        $next_id = $this->db->nextId(self::LOG_TABLE_NAME);
        $vals = [
            "id" => ["integer", $next_id],
            "crs_ref_id" => ["integer", $crs_ref_id],
            "date" => ["text", $today],
            "state" => ["text", self::FAIL],
            "message" => ["text", $message]
        ];

        $this->db->insert(self::LOG_TABLE_NAME, $vals);
    }

    /**
     * @inheritdoc
     */
    public function getSuccessLogEntries() : array
    {
        $query = <<<EOT
SELECT id, crs_ref_id, date
FROM acwaiting_log
WHERE state = 'success'
EOT;
        $res = $this->db->query($query);

        $ret = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $ret[] = new Entry(
                (int) $row["id"],
                (int) $row["crs_ref_id"],
                new \DateTime($row["date"]),
                ""
            );
        }

        return $ret;
    }

    /**
     * @inheritdoc
     */
    public function getFailedLogEntries() : array
    {
        $query = <<<EOT
SELECT id, crs_ref_id, date, message
FROM acwaiting_log
WHERE state = 'fail'
EOT;
        $res = $this->db->query($query);

        $ret = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $ret[] = new Entry(
                (int) $row["id"],
                (int) $row["crs_ref_id"],
                new \DateTime($row["date"]),
                $row["message"]
            );
        }

        return $ret;
    }

    public function resolveConflictFor(int $id)
    {
        $query = <<<EOT
DELETE FROM acwaiting_log
WHERE id = $id
EOT;
        $this->db->manipulate($query);
    }

    public function createLogTable()
    {
        if (!$this->db->tableExists(self::LOG_TABLE_NAME)) {
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
                    "date" => array(
                        "type" => "text",
                        "length" => 12,
                        "notnull" => true
                    ),
                    "state" => array(
                        "type" => "text",
                        "length" => 20,
                        "notnull" => true
                    ),
                    "message" => array(
                        "type" => "clob",
                        "notnull" => false
                    )
                );

            $this->db->createTable(self::LOG_TABLE_NAME, $fields);
        }
    }

    public function createSequence()
    {
        if (!$this->db->tableExists(self::LOG_TABLE_NAME . "_seq")) {
            $this->db->createSequence(self::LOG_TABLE_NAME);
        }
    }

    public function createPrimaryKey()
    {
        try {
            $this->db->addPrimaryKey(self::LOG_TABLE_NAME, array("id"));
        } catch (\PDOException $e) {
            $this->db->dropPrimaryKey(self::LOG_TABLE_NAME);
            $this->db->addPrimaryKey(self::LOG_TABLE_NAME, array("id"));
        }
    }
}
