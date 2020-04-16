<?php

declare(strict_types=1);

namespace CaT\Plugins\EduBiography\Config\OverviewCertificate\Activation;

use PHPUnit\Framework\TestCase;

class ilDB implements DB
{
    const TABLE_NAME = "xebr_acc_document";
    /**
     * @var \ilDBInterface
     */
    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritDoc
     */
    public function insert(bool $active, int $usr_id, \DateTime $date)
    {
        $id = $this->getNextId();
        $values = [
            "id" => [
                "integer",
                $id
            ],
            "active" => [
                "integer",
                $active
            ],
            "changed_by" => [
                "integer",
                $usr_id
            ],
            "changed_at" => [
                "text",
                $date->format("Y-m-d")
            ]
        ];

        $this->db->insert(self::TABLE_NAME, $values);
    }

    /**
     * @inheritDoc
     */
    public function select() : Active
    {
        $query = "SELECT active" . PHP_EOL
            . " FROM " . self::TABLE_NAME . PHP_EOL
            . " ORDER BY id DESC" . PHP_EOL
            . " LIMIT 1"
        ;

        $res = $this->db->query($query);
        if ($this->db->numRows($res) == 0) {
            return new Active(false);
        }

        $row = $this->db->fetchAssoc($res);
        return new Active((bool) $row["active"]);
    }

    public function createTable()
    {
        if (!$this->db->tableExists("xebr_acc_document")) {
            $fields = [
                "id" => [
                    'type' => 'integer',
                    'length' => 4,
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

            $this->db->createTable("xebr_acc_document", $fields);
        }
    }

    public function createSequence()
    {
        if (!$this->db->sequenceExists("xebr_acc_document")) {
            $this->db->createSequence("xebr_acc_document");
        }
    }

    public function createPrimaryKey()
    {
        try {
            $this->db->addPrimaryKey("xebr_acc_document", ["id"]);
        } catch (\PDOException $e) {
            $this->db->dropPrimaryKey("xebr_acc_document");
            $this->db->addPrimaryKey("xebr_acc_document", ["id"]);
        }
    }

    protected function getNextId() : int
    {
        return (int) $this->db->nextId(self::TABLE_NAME);
    }
}
