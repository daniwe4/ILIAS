<?php

declare(strict_types=1);

namespace CaT\Plugins\EduBiography\Config\OverviewCertificate\Schedules;

use Symfony\Component\Console\Exception\LogicException;

class ilDB implements DB
{
    const TABLE_NAME = "xebr_cert_schedules";

    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var \ilObjUser
     */
    protected $user;

    public function __construct(\ilDBInterface $db, \ilObjUser $user)
    {
        $this->db = $db;
        $this->user = $user;
    }

    /**
     * @inheritDoc
     */
    public function create(
        string $title,
        \DateTime $start,
        \DateTime $end,
        int $min_idd_value,
        bool $part_document
    ) {
        $next_id = $this->getNextId();
        $values = [
            "id" => [
                "integer",
                $next_id
            ],
            "title" => [
                "text",
                $title
            ],
            "start" => [
                "text",
                $start->format("Y-m-d")
            ],
            "end" => [
                "text",
                $end->format("Y-m-d")
            ],
            "min_idd_value" => [
                "integer",
                $min_idd_value
            ],
            "changed_by" => [
                "integer",
                $this->user->getId()
            ],
            "changed_at" => [
                "text",
                date("Y-m-d")
            ],
            "part_document" => [
                "integer",
                $part_document
            ]
        ];

        $this->db->insert(self::TABLE_NAME, $values);
    }

    public function update(Schedule $schedule)
    {
        $where = [
            "id" => [
                "integer",
                $schedule->getId()
            ]
        ];
        $values = [
            "title" => [
                "text",
                $schedule->getTitle()
            ],
            "start" => [
                "text",
                $schedule->getStart()->format("Y-m-d")
            ],
            "end" => [
                "text",
                $schedule->getEnd()->format("Y-m-d")
            ],
            "min_idd_value" => [
                "integer",
                $schedule->getMinIddValue()
            ],
            "changed_by" => [
                "integer",
                $this->user->getId()
            ],
            "changed_at" => [
                "text",
                date("Y-m-d")
            ],
            "part_document" => [
                "integer",
                $schedule->isParticipationsDocumentActive()
            ]
        ];

        $this->db->update(self::TABLE_NAME, $values, $where);
    }

    /**
     * @inheritDoc
     */
    public function selectAllBy(
        string $field,
        string $direction,
        int $limit,
        int $offset
    ) : array {
        $q = "SELECT id, title, start, end, min_idd_value, active, part_document" . PHP_EOL
            . " FROM " . self::TABLE_NAME . PHP_EOL
            . " WHERE deleted = 0" . PHP_EOL
            . " ORDER BY " . $field . " " . $direction . PHP_EOL
            . " LIMIT " . $limit . " OFFSET " . $offset
        ;

        $res = $this->db->query($q);
        $ret = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $ret[] = $this->createScheduleObject(
                (int) $row["id"],
                (string) $row["title"],
                \DateTime::createFromFormat("Y-m-d", $row["start"]),
                \DateTime::createFromFormat("Y-m-d", $row["end"]),
                (int) $row["min_idd_value"],
                (bool) $row["active"],
                (bool) $row["part_document"]
            );
        }

        return $ret;
    }

    /**
     * @inheritDoc
     */
    public function selectFor(int $id) : Schedule
    {
        $q = "SELECT id, title, start, end, min_idd_value, active, part_document" . PHP_EOL
            . " FROM " . self::TABLE_NAME . PHP_EOL
            . " WHERE id = " . $this->db->quote($id, "integer") . PHP_EOL
            . "     AND deleted = 0"
        ;

        $res = $this->db->query($q);
        if ($this->db->numRows($res) == 0) {
            throw new LogicException("No object found for id: " . $id);
        }

        $row = $this->db->fetchAssoc($res);
        return $this->createScheduleObject(
            (int) $row["id"],
            (string) $row["title"],
            \DateTime::createFromFormat("Y-m-d", $row["start"]),
            \DateTime::createFromFormat("Y-m-d", $row["end"]),
            (int) $row["min_idd_value"],
            (bool) $row["active"],
            (bool) $row["part_document"]
        );
    }

    /**
     * @inheritDoc
     */
    public function isTitleInUse(string $title) : bool
    {
        $sql =
             'SELECT title' . PHP_EOL
            . ' FROM ' . self::TABLE_NAME . PHP_EOL
            . ' WHERE title = ' . $this->db->quote($title, 'text') . PHP_EOL
            . ' AND deleted = 0'
        ;

        $result = $this->db->query($sql);

        return $this->db->numRows($result) > 0;
    }

    /**
     * @inheritDoc
     */
    public function setActiveStatus(int $id, bool $active)
    {
        $where = [
            "id" => [
                "integer",
                $id
            ]
        ];

        $values = [
            "active" => [
                "integer",
                $active
            ]
        ];

        $this->db->update(self::TABLE_NAME, $values, $where);
    }

    /**
     * @inheritDoc
     */
    public function deleteFor(int $id)
    {
        $where = [
            "id" => [
                "integer",
                $id
            ]
        ];

        $values = [
            "deleted" => [
                "integer",
                1
            ]
        ];

        $this->db->update(self::TABLE_NAME, $values, $where);
    }

    /**
     * @return Schedule[]
     */
    public function getAllActiveScheduled(array $obj_ids) : array
    {
        $q = "SELECT id, title, start, end, min_idd_value, active, part_document" . PHP_EOL
            . " FROM " . self::TABLE_NAME . PHP_EOL
            . " WHERE ((active = 1 OR part_document = 1) AND deleted = 0)" . PHP_EOL
            . "     OR " . $this->db->in("id", $obj_ids, false, "integer") . PHP_EOL
            . " ORDER BY start DESC"
        ;

        $res = $this->db->query($q);
        $ret = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $ret[] = $this->createScheduleObject(
                (int) $row["id"],
                (string) $row["title"],
                \DateTime::createFromFormat("Y-m-d", $row["start"]),
                \DateTime::createFromFormat("Y-m-d", $row["end"]),
                (int) $row["min_idd_value"],
                (bool) $row["active"],
                (bool) $row["part_document"]
            );
        }

        return $ret;
    }

    protected function createScheduleObject(
        int $id,
        string $title,
        \DateTime $start,
        \DateTime $end,
        int $min_idd_value,
        bool $active,
        bool $part_document
    ) : Schedule {
        return new Schedule(
            $id,
            $title,
            $start,
            $end,
            $min_idd_value,
            $active,
            $part_document
        );
    }

    protected function getNextId() : int
    {
        return (int) $this->db->nextId(self::TABLE_NAME);
    }

    public function createTable()
    {
        if (!$this->db->tableExists("xebr_cert_schedules")) {
            $fields = [
                "id" => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ],
                "title" => [
                    'type' => 'clob',
                    'notnull' => true
                ],
                "start" => [
                    'type' => 'text',
                    'length' => 21,
                    'notnull' => true
                ],
                "end" => [
                    'type' => 'text',
                    'length' => 21,
                    'notnull' => true
                ],
                "min_idd_value" => [
                    'type' => 'integer',
                    'length' => 8,
                    'notnull' => true
                ],
                "active" => [
                    'type' => 'integer',
                    'length' => 1,
                    'notnull' => true
                ],
                "changed_by" => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ],
                "changed_at" => [
                    'type' => 'text',
                    'length' => 21,
                    'notnull' => true
                ]
            ];

            $this->db->createTable("xebr_cert_schedules", $fields);
        }
    }

    public function createSequence()
    {
        if (!$this->db->sequenceExists("xebr_cert_schedules")) {
            $this->db->createSequence("xebr_cert_schedules");
        }
    }

    public function createPrimaryKey()
    {
        try {
            $this->db->addPrimaryKey("xebr_cert_schedules", ["id"]);
        } catch (\PDOException $e) {
            $this->db->dropPrimaryKey("xebr_cert_schedules");
            $this->db->addPrimaryKey("xebr_cert_schedules", ["id"]);
        }
    }

    public function update1()
    {
        if (!$this->db->tableColumnExists("xebr_cert_schedules", "participatio")) {
            $field = [
                'type' => 'integer',
                'length' => 1,
                'notnull' => true,
                'default' => 0
            ];

            $this->db->addTableColumn("xebr_cert_schedules", "deleted", $field);
        }
    }

    public function update2()
    {
        if (!$this->db->tableColumnExists("xebr_cert_schedules", "part_document")) {
            $field = [
                'type' => 'integer',
                'length' => 1,
                'notnull' => true,
                'default' => 0
            ];

            $this->db->addTableColumn("xebr_cert_schedules", "part_document", $field);
        }
    }
}
