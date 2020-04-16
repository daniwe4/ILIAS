<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\TrainingSearch\Settings;

class ilDB implements DB
{
    const TABLE_NAME = "xtrs_settings";
    const TABLE_NAME_TOPICS = "xtrs_settings_topics";
    const TABLE_NAME_CATEGORIES = "xtrs_settings_cats";
    const TABLE_NAME_TARGET_GROUPS = "xtrs_settings_t_g";

    /**
     * @var \ilDBInterface
     */
    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function create(int $obj_id) : Settings
    {
        $settings = new Settings($obj_id, false);
        $values = [
            "obj_id" => ["integer", $settings->getObjId()],
            "is_online" => ["integer", $settings->getIsOnline()],
            "is_local" => ["integer", $settings->isLocal()],
            "is_recommendation_allowed" => ["integer", $settings->isRecommendationAllowed()]
        ];

        $this->db->insert(self::TABLE_NAME, $values);
        $this->insertArrayIn(
            self::TABLE_NAME_TOPICS,
            $obj_id,
            $settings->relevantTopics()
        );

        $this->insertArrayIn(
            self::TABLE_NAME_CATEGORIES,
            $obj_id,
            $settings->relevantCategories()
        );

        $this->insertArrayIn(
            self::TABLE_NAME_TARGET_GROUPS,
            $obj_id,
            $settings->relevantTargetGroups()
        );

        return $settings;
    }

    public function select(int $obj_id) : Settings
    {
        $query = "SELECT obj_id,  is_online, is_local, is_recommendation_allowed" . PHP_EOL
            . "FROM " . self::TABLE_NAME . PHP_EOL
            . "WHERE obj_id = " . $this->db->quote($obj_id, "integer");

        $res = $this->db->query($query);

        if ($this->db->numRows($res) == 0) {
            return $this->create($obj_id);
        }

        $row = $this->db->fetchAssoc($res);
        return new Settings(
            (int) $row["obj_id"],
            (bool) $row["is_online"],
            (bool) $row["is_local"],
            $this->selectTopics($obj_id),
            $this->selectCategories($obj_id),
            $this->selectTargetGroups($obj_id),
            (bool) $row["is_recommendation_allowed"]
        );
    }

    public function update(Settings $settings)
    {
        $where = [
            "obj_id" => ["integer", $settings->getObjId()]
        ];

        $values = [
            "is_online" => ["integer", $settings->getIsOnline()],
            "is_local" => ["integer", $settings->isLocal()],
            "is_recommendation_allowed" => ["integer", $settings->isRecommendationAllowed()]
        ];

        $this->db->update(self::TABLE_NAME, $values, $where);
        $this->updateTopics($settings);
        $this->updateCategories($settings);
        $this->updateTargetGroups($settings);
    }

    public function delete(int $obj_id)
    {
        $query = "DELETE FROM " . self::TABLE_NAME . PHP_EOL
            . " WHERE obj_id = " . $this->db->quote($obj_id, "integer");

        $this->db->manipulate($query);
        $this->trunkateByObjIdIn(self::TABLE_NAME_TOPICS, $obj_id);
        $this->trunkateByObjIdIn(self::TABLE_NAME_CATEGORIES, $obj_id);
        $this->trunkateByObjIdIn(self::TABLE_NAME_TARGET_GROUPS, $obj_id);
    }

    public function createTable()
    {
        if (!$this->db->tableExists(self::TABLE_NAME)) {
            $fields = array(
                'obj_id' => array(
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
            $this->db->addPrimaryKey(self::TABLE_NAME, array("obj_id"));
        } catch (\PDOException $e) {
            $this->db->dropPrimaryKey(self::TABLE_NAME);
            $this->db->addPrimaryKey(self::TABLE_NAME, array("obj_id"));
        }
    }

    public function update1()
    {
        if (!$this->db->tableColumnExists(self::TABLE_NAME, "is_local")) {
            $field = array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => false,
                'default' => 0
            );

            $this->db->addTableColumn(self::TABLE_NAME, "is_local", $field);
        }
    }

    protected function selectTopics(int $obj_id) : array
    {
        return $this->selectArrayFrom(self::TABLE_NAME_TOPICS, $obj_id);
    }

    protected function selectCategories(int $obj_id) : array
    {
        return $this->selectArrayFrom(self::TABLE_NAME_CATEGORIES, $obj_id);
    }

    protected function selectTargetGroups(int $obj_id) : array
    {
        return $this->selectArrayFrom(self::TABLE_NAME_TARGET_GROUPS, $obj_id);
    }

    protected function selectArrayFrom(string $table_name, int $obj_id) : array
    {
        $q = 'SELECT val_id'
            . '	FROM ' . $table_name
            . '	WHERE obj_id = ' . $this->db->quote($obj_id, 'integer');
        $res = $this->db->query($q);
        $return = [];
        while ($rec = $this->db->fetchAssoc($res)) {
            $return[] = (int) $rec['val_id'];
        }
        return $return;
    }

    protected function updateTopics(Settings $settings)
    {
        $this->updateArrayIn(
            self::TABLE_NAME_TOPICS,
            $settings->getObjId(),
            $settings->relevantTopics()
        );
    }

    protected function updateCategories(Settings $settings)
    {
        $this->updateArrayIn(
            self::TABLE_NAME_CATEGORIES,
            $settings->getObjId(),
            $settings->relevantCategories()
        );
    }

    protected function updateTargetGroups(Settings $settings)
    {
        $this->updateArrayIn(
            self::TABLE_NAME_TARGET_GROUPS,
            $settings->getObjId(),
            $settings->relevantTargetGroups()
        );
    }

    protected function updateArrayIn(string $table, int $obj_id, array $values)
    {
        $this->trunkateByObjIdIn($table, $obj_id);
        $this->insertArrayIn($table, $obj_id, $values);
    }

    protected function trunkateByObjIdIn(string $table, int $obj_id)
    {
        $this->db->manipulate(
            'DELETE FROM ' . $table
            . '	WHERE obj_id = ' . $this->db->quote($obj_id, 'integer')
        );
    }

    protected function insertArrayIn(string $table, int $obj_id, array $values)
    {
        foreach ($values as $val_id) {
            $this->db->insert(
                $table,
                [
                    'row_id' => ['integer', $this->db->nextId($table)],
                    'obj_id' => ['integer', $obj_id],
                    'val_id' => ['integer', $val_id]
                ]
            );
        }
    }

    public function update2()
    {
        if (!$this->db->tableColumnExists(self::TABLE_NAME, "is_recommendation_allowed")) {
            $field = array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => false,
                'default' => 1
            );
            $this->db->addTableColumn(self::TABLE_NAME, "is_recommendation_allowed", $field);
        }
    }
}
