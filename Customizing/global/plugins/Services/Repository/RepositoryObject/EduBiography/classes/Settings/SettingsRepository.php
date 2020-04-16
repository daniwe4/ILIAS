<?php

namespace CaT\Plugins\EduBiography\Settings;

class SettingsRepository
{
    const DB_TABLE = 'xebr_settings';

    const PROP_IS_ONLINE = 'is_online';
    const PROP_HAS_SUPERIOR_OVERVIEW = 'has_sup_overview';
    const PROP_ID = 'id';
    const PROP_RECOMMENDATION_ALLOWED = "recommendation_allowed";
    const PROP_INIT_VISIBLE_COLUMNS = "init_visible_columns";
    const PROP_INVISIBLE_COURSE_TOPICS = "invisible_crs_topics";

    /**
     * @var \ilDBInterface
     */
    protected $db;
    /**
     * @var SettingsRepository
     */
    protected static $instance;

    /**
     * @param	\ilDBInterface	$db
     */
    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            global $DIC;
            self::$instance = new self($DIC['ilDB']);
        }
        return self::$instance;
    }

    public function createSettings(int $id)
    {
        $this->create($id);
        return new Settings($id, false, false, [], []);
    }

    public function loadSettings(int $id)
    {
        $settings = $this->loadSettingsArray($id);
        return new Settings(
            $id,
            $settings[self::PROP_IS_ONLINE],
            $settings[self::PROP_HAS_SUPERIOR_OVERVIEW],
            $settings[self::PROP_INIT_VISIBLE_COLUMNS],
            $settings[self::PROP_INVISIBLE_COURSE_TOPICS],
            $settings[self::PROP_RECOMMENDATION_ALLOWED]
        );
    }

    public function updateSettings(Settings $settings)
    {
        $this->updateSettingsArray(
            [
                self::PROP_ID => $settings->id(),
                self::PROP_IS_ONLINE => $settings->isOnline(),
                self::PROP_HAS_SUPERIOR_OVERVIEW => $settings->hasSuperiorOverview(),
                self::PROP_INIT_VISIBLE_COLUMNS => $settings->getInitVisibleColumns(),
                self::PROP_INVISIBLE_COURSE_TOPICS => $settings->getInvisibleCourseTopics(),
                self::PROP_RECOMMENDATION_ALLOWED => $settings->getRecommendationAllowed()
            ]
        )
        ;
    }

    public function deleteSettings(Settings $settings)
    {
        $this->deleteSettingsEntry($settings->id());
    }

    public function getHistoricCourseTopics() : array
    {
        $ret = [];

        $query = "SELECT DISTINCT list_data FROM hhd_crs_topics";
        $res = $this->db->query($query);

        while ($row = $this->db->fetchAssoc($res)) {
            $ret[$row["list_data"]] = $row["list_data"];
        }

        return $ret;
    }

    protected function create(int $id)
    {
        $values = [
            self::PROP_ID => [
                'integer',
                $id
            ],
            self::PROP_IS_ONLINE => [
                'integer',
                0
            ],
            self::PROP_HAS_SUPERIOR_OVERVIEW => [
                'integer',
                0
            ],
            self::PROP_INIT_VISIBLE_COLUMNS => [
                'text',
                null
            ],
            self::PROP_RECOMMENDATION_ALLOWED => [
                'integer',
                0
            ]
        ];
        $this->db->insert(self::DB_TABLE, $values);
    }

    protected function loadSettingsArray(int $id)
    {
        $q = 'SELECT ' . self::PROP_IS_ONLINE . ', ' . self::PROP_HAS_SUPERIOR_OVERVIEW . ', ' . self::PROP_INIT_VISIBLE_COLUMNS . PHP_EOL
            . ',' . self::PROP_INVISIBLE_COURSE_TOPICS . PHP_EOL . ', ' . self::PROP_RECOMMENDATION_ALLOWED . ' '
            . 'FROM ' . self::DB_TABLE . PHP_EOL
            . 'WHERE ' . self::PROP_ID . ' = ' . $this->db->quote($id, 'integer');

        $rec = $this->db->fetchAssoc($this->db->query($q));
        if ($rec) {
            $rec[self::PROP_IS_ONLINE] = (bool) $rec[self::PROP_IS_ONLINE];
            $rec[self::PROP_HAS_SUPERIOR_OVERVIEW] = (bool) $rec[self::PROP_HAS_SUPERIOR_OVERVIEW];
            $columns = $rec[self::PROP_INIT_VISIBLE_COLUMNS];
            if (is_null($columns)) {
                $columns = [];
            } else {
                $columns = unserialize($columns);
            }
            $rec[self::PROP_INIT_VISIBLE_COLUMNS] = $columns;

            $columns = $rec[self::PROP_INVISIBLE_COURSE_TOPICS];
            if (is_null($columns)) {
                $columns = [];
            } else {
                $columns = unserialize($columns);
            }
            $rec[self::PROP_INVISIBLE_COURSE_TOPICS] = $columns;
            $rec[self::PROP_RECOMMENDATION_ALLOWED] = (bool) $rec[self::PROP_RECOMMENDATION_ALLOWED];

            return $rec;
        }
        return [];
    }

    protected function updateSettingsArray(array $settings)
    {
        if (!is_int($settings[self::PROP_ID])) {
            throw new \InvalidArgumentEsxception('No id given');
        }

        $values = [
            self::PROP_IS_ONLINE => [
                'integer',
                $settings[self::PROP_IS_ONLINE]
            ],
            self::PROP_HAS_SUPERIOR_OVERVIEW => [
                'integer',
                $settings[self::PROP_HAS_SUPERIOR_OVERVIEW]
            ],
            self::PROP_INIT_VISIBLE_COLUMNS => [
                'text',
                serialize($settings[self::PROP_INIT_VISIBLE_COLUMNS])
            ],
            self::PROP_INVISIBLE_COURSE_TOPICS => [
                'text',
                serialize($settings[self::PROP_INVISIBLE_COURSE_TOPICS])
            ],
            self::PROP_RECOMMENDATION_ALLOWED => [
                'integer',
                $settings[self::PROP_RECOMMENDATION_ALLOWED]
            ]
        ];

        $where = [
            self::PROP_ID => [
                'integer',
                $settings[self::PROP_ID]
            ]
        ];

        $this->db->update(self::DB_TABLE, $values, $where);
    }

    protected function deleteSettingsEntry($id)
    {
        $this->db->manipulate('DELETE FROM ' . self::DB_TABLE
                            . '	WHERE ' . self::PROP_ID . ' = ' . $this->db->quote($id, 'integer'));
    }

    public function update1()
    {
        if (!$this->db->tableColumnExists(self::DB_TABLE, "init_visible_columns")) {
            $field = [
                "type" => "clob",
                "default" => null,
                "notnull" => false
            ];

            $this->db->addTableColumn(self::DB_TABLE, "init_visible_columns", $field);
        }
    }

    public function update2()
    {
        if (!$this->db->tableColumnExists(self::DB_TABLE, "invisible_crs_topics")) {
            $field = [
                "type" => "clob",
                "default" => null,
                "notnull" => false
            ];

            $this->db->addTableColumn(self::DB_TABLE, "invisible_crs_topics", $field);
        }
    }

    public function update3()
    {
        if (!$this->db->tableColumnExists(self::DB_TABLE, "recommendation_allowed")) {
            $field = array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => false,
                'default' => 1
            );
            $this->db->addTableColumn(self::DB_TABLE, "recommendation_allowed", $field);
        }
    }
}
