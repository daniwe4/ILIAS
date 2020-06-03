<?php

declare(strict_types=1);

namespace CaT\Plugins\WBDManagement\Settings;

class ilDB implements DB
{
    const TABLE_NAME = "xwbm_settings";

    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function update(WBDManagement $settings)
    {
        $obj_id = $settings->getObjId();
        $where = ["obj_id" => ["integer", $obj_id]];

        $values = [
            "online" => ["integer", $settings->isOnline()],
            "document_path" => ["text", $settings->getDocumentPath()],
            "email" => ["text", $settings->getEmail()]

        ];

        $this->getDB()->update(self::TABLE_NAME, $values, $where);
    }

    public function create(int $obj_id)
    {
        $settings = new WBDManagement($obj_id);

        $values = [
            "obj_id" => ["integer", $settings->getObjId()],
            "online" => ["integer", $settings->isOnline()],
            "document_path" => ["integer", $settings->getDocumentPath()],
            "email" => ["text", $settings->getEmail()]
        ];

        $this->getDB()->insert(self::TABLE_NAME, $values);

        return $settings;
    }

    public function selectFor(int $obj_id) : WBDManagement
    {
        $where = "WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");

        $query =
            "SELECT obj_id, online, document_path, email" . PHP_EOL
            . "FROM " . self::TABLE_NAME . PHP_EOL;

        $result = $this->getDB()->query($query . $where);

        if ($this->getDB()->numRows($result) == 0) {
            throw new \LogicException(__METHOD__ . " no Settings found for obj_id " . $obj_id);
        }

        return $this->getSettingsObject($this->getDB()->fetchAssoc($result));
    }

    protected function getSettingsObject(array $row) : WBDManagement
    {
        return new WBDManagement(
            (int) $row["obj_id"],
            (bool) $row["online"],
            $row["document_path"] ?? "",
            $row["email"] ?? ""
        );
    }

    public function deleteFor(int $obj_id)
    {
        $where = "WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");
        $query = "DELETE FROM " . self::TABLE_NAME . PHP_EOL;

        $this->getDB()->manipulate($query . $where);
    }

    public function createTable()
    {
        if (!$this->getDB()->tableExists(self::TABLE_NAME)) {
            $fields = [
                'obj_id' => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ],
                'online' => [
                    'type' => 'integer',
                    'length' => 1,
                ]
            ];

            $this->getDB()->createTable(self::TABLE_NAME, $fields);
        }
    }

    public function createPrimaryKey()
    {
        $this->getDB()->addPrimaryKey(self::TABLE_NAME, ["obj_id"]);
    }

    public function createSequence()
    {
        $this->getDB()->createSequence(self::TABLE_NAME);
    }

    /**
     * @throws \Exception
     */
    private function getDB() : \ilDBInterface
    {
        if (!$this->db) {
            throw new \Exception("no database");
        }
        return $this->db;
    }

    public function update1()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "document_path")) {
            $this->getDB()->addTableColumn(
                self::TABLE_NAME,
                "document_path",
                [
                    "type" => "text",
                    "length" => "512"
                ]
            );
        }
    }

    public function update2()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "email")) {
            $this->getDB()->addTableColumn(
                self::TABLE_NAME,
                "email",
                [
                    "type" => "clob"
                ]
            );
        }
    }

    public function update3()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "show_in_cockpit")) {
            $this->getDB()->dropTableColumn(self::TABLE_NAME, "show_in_cockpit");
        }
    }
}
