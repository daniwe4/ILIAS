<?php

declare(strict_types=1);

namespace CaT\Plugins\Accounting\Config\Cancellation\Scale;

use \LogicException;
use \ilDBInterface;

class ilDB implements DB
{
    const TABLE_NAME = "xacc_config_scale";

    /**
     * @var ilDBInterface
     */
    protected $db;

    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritDoc
     */
    public function addScale(int $span_start, int $span_end, int $percent) : Scale
    {
        $next_id = (int) $this->db->nextId(self::TABLE_NAME);

        $scale = new Scale(
            $next_id,
            $span_start,
            $span_end,
            $percent
        );

        $values = [
            "id" => [
                "integer",
                $next_id
            ],
            "span_start" => [
                "integer",
                $span_start
            ],
            "span_end" => [
                "integer",
                $span_end
            ],
            "percent" => [
                "integer",
                $percent
            ]
        ];

        $this->db->insert(self::TABLE_NAME, $values);

        return $scale;
    }

    public function update(Scale $scale)
    {
        $where = [
            "id" => [
                "integer",
                $scale->getId()
            ]
        ];

        $values = [
            "span_start" => [
                "integer",
                $scale->getSpanStart()
            ],
            "span_end" => [
                "integer",
                $scale->getSpanEnd()
            ],
            "percent" => [
                "integer",
                $scale->getPercent()
            ]
        ];

        $this->db->update(self::TABLE_NAME, $values, $where);
    }

    /**
     * @inheritDoc
     */
    public function getScales() : array
    {
        $table = self::TABLE_NAME;
        $query = <<<SQL
SELECT id, span_start, span_end, percent
FROM $table
SQL;
        $ret = [];
        $res = $this->db->query($query);
        while ($row = $this->db->fetchAssoc($res)) {
            $ret[] = new Scale(
                (int) $row["id"],
                (int) $row["span_start"],
                (int) $row["span_end"],
                (int) $row["percent"]
            );
        }

        return $ret;
    }

    /**
     * @inheritDoc
     */
    public function getScaleFor(int $days) : Scale
    {
        $table = self::TABLE_NAME;
        $days = $this->db->quote($days, "integer");
        $query = <<<SQL
SELECT id, span_start, span_end, percent
FROM $table
WHERE span_start >= $days
  AND span_end <= $days
SQL;
        $res = $this->db->query($query);

        if ($this->db->numRows($res) == 0) {
            throw new LogicException("No scale found for days: " . $days);
        }

        $row = $this->db->fetchAssoc($res);
        return new Scale(
            (int) $row["id"],
            (int) $row["span_start"],
            (int) $row["span_end"],
            (int) $row["percent"]
        );
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id)
    {
        $table = self::TABLE_NAME;
        $id = $this->db->quote($id, "integer");
        $query = <<<SQL
DELETE FROM $table
WHERE id = $id;
SQL;
        var_dump($query);
        $this->db->manipulate($query);
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
                "span_start" => [
                    "type" => "integer",
                    "length" => 4,
                    "notnull" => true
                ],
                "span_end" => [
                    "type" => "integer",
                    "length" => 4,
                    "notnull" => true
                ],
                "percent" => [
                    "type" => "integer",
                    "length" => 4,
                    "notnull" => true
                ]
            ];
            $this->db->createTable(self::TABLE_NAME, $fields);
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

    public function createSequence()
    {
        if (!$this->db->sequenceExists(self::TABLE_NAME)) {
            $this->db->createSequence(self::TABLE_NAME);
        }
    }
}
