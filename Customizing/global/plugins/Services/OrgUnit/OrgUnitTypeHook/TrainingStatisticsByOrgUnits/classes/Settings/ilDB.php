<?php

namespace CaT\Plugins\TrainingStatisticsByOrgUnits\Settings;

class ilDB implements DB
{
    const TABLE_NAME = "xtou_settings";

    /**
     * @var \ilDBInterface
     */
    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function createSettingsFor(int $id) : Settings
    {
        $settings = new Settings($id);

        $values = [
            "id" => [
                "integer",
                $id
            ],
            "is_online" => [
                "integer",
                $settings->isOnline()
            ],
            "is_global" => [
                "integer",
                $settings->isGlobal()
            ]
        ];

        $this->db->insert(self::TABLE_NAME, $values);

        return $settings;
    }

    public function updateSettings(Settings $settings)
    {
        $where = [
            "id" => [
                "integer",
                $settings->getId()
            ]
        ];

        $values = [
            "is_online" => [
                "integer",
                $settings->isOnline()
            ],
            "is_global" => [
                "integer",
                $settings->isGlobal()
            ]
        ];

        $this->db->update(self::TABLE_NAME, $values, $where);
    }

    public function selectSettingsFor(int $id) : Settings
    {
        $table = self::TABLE_NAME;
        $id = $this->db->quote($id, "integer");

        $query = <<<SQL
SELECT is_online,is_global
FROM $table
WHERE id = $id
SQL;

        $res = $this->db->query($query);
        $row = $this->db->fetchAssoc($res);
        return new Settings(
            $id,
            (bool) $row["is_online"],
            (bool) $row["is_global"]
        );
    }

    public function deleteSettingsFor(int $id)
    {
        $table = self::TABLE_NAME;
        $id = $this->db->quote($id, "integer");
        $query = <<<SQL
DELETE FROM $table
WHERE id = $id
SQL;

        $this->db->manipulate($query);
    }

    public function createTable()
    {
        if (!$this->db->tableExists(self::TABLE_NAME)) {
            $fields =
                array('id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'is_online' => array(
                    'type' => 'integer',
                    'length' => 1,
                    'notnull' => true
                )
            );

            $this->db->createTable(self::TABLE_NAME, $fields);
        }
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
