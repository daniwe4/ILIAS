<?php

declare(strict_types=1);

namespace CaT\Plugins\CourseCreation\CreationSettings;

class ilDB implements DB
{
    const TABLE_NAME = "xccr_create_settings";

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
     * @inheritdoc
     */
    public function save(array $roles)
    {
        $id = $this->db->nextId(self::TABLE_NAME);
        $values = [
            "id" => [
                "integer",
                $id
            ],
            "roles" => [
                "text",
                json_encode($roles)
            ],
            "changed_by" => [
                "integer",
                $this->user->getId()
            ],
            "changed_date" => [
                "text",
                date("Y-m-d H:i:s")
            ]
        ];

        $this->db->insert(self::TABLE_NAME, $values);
    }

    /**
     * @inheritdoc
     */
    public function select() : array
    {
        $query = "SELECT roles" . PHP_EOL
            . " FROM " . self::TABLE_NAME . PHP_EOL
            . " ORDER BY id DESC" . PHP_EOL
            . " LIMIT 1"
        ;

        $res = $this->db->query($query);
        $row = $this->db->fetchAssoc($res);

        if (is_null($row["roles"])) {
            return [];
        }

        return json_decode($row["roles"]);
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
                "roles" => [
                    "type" => "clob",
                    "notnull" => true
                ],
                "changed_by" => [
                    "type" => "integer",
                    "length" => 4,
                    "notnull" => true
                ],
                "changed_date" => [
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
        $this->db->createSequence(self::TABLE_NAME);
    }

    public function addPrimaryKey()
    {
        try {
            $this->db->addPrimaryKey(self::TABLE_NAME, array("id"));
        } catch (\PDOException $e) {
            $this->db->dropPrimaryKey(self::TABLE_NAME);
            $this->db->addPrimaryKey(self::TABLE_NAME, array("id"));
        }
    }
}
