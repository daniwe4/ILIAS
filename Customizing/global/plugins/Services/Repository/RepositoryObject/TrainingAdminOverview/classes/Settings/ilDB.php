<?php

namespace CaT\Plugins\TrainingAdminOverview\Settings;

class ilDB implements DB
{
    const TABLE_NAME = "xado_settings";

    /**
     * @var \ilDBInterface
     */
    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function createSettings(int $obj_id, bool $show_info_tab = false)
    {
        $settings = new Settings($obj_id, $show_info_tab);

        $values = [
            'obj_id' => ['integer', $settings->getObjId()],
            'show_info_tab' => ['integer', $settings->getShowInfoTab()]
        ];

        $this->getDB()->insert(self::TABLE_NAME, $values);

        return $settings;
    }

    public function selectByObjId(int $obj_id) : Settings
    {
        $sql =
             'SELECT obj_id, show_info_tab' . PHP_EOL
            . 'FROM ' . self::TABLE_NAME . PHP_EOL
            . 'WHERE obj_id = ' . $this->getDB()->quote($obj_id, 'integer') . PHP_EOL
        ;

        $result = $this->getDB()->query($sql);

        if ($this->getDB()->numRows($result) == 0) {
            return new Settings($obj_id);
        }

        return $this->getSettingsObject($this->getDB()->fetchAssoc($result));
    }


    public function updateSettings(Settings $settings)
    {
        $obj_id = $settings->getObjId();
        $where = ['obj_id' => ['integer', $obj_id]];
        $values = ['show_info_tab' => ['integer', (int) $settings->getShowInfoTab()]];
        $this->getDB()->replace(self::TABLE_NAME, $where, $values);
    }

    public function delete(int $obj_id)
    {
        $sql =
            'DELETE FROM ' . self::TABLE_NAME . PHP_EOL
            . "WHERE obj_id = " . $this->getDB()->quote($obj_id, 'integer') . PHP_EOL
        ;
        $this->getDB()->manipulate($sql);
    }

    protected function getSettingsObject(array $row)
    {
        return new Settings(
            $row['obj_id'],
            $row['show_info_tab']
        );
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
                'show_info_tab' => [
                    'type' => 'integer',
                    'length' => 1,
                    'notnull' => true,
                    'default' => 0
                ]
            ];

            $this->getDB()->createTable(self::TABLE_NAME, $fields);
        }
    }

    public function createPrimaryKey()
    {
        $this->getDB()->addPrimaryKey(self::TABLE_NAME, ['obj_id']);
    }

    protected function getDB()
    {
        if (!$this->db) {
            throw new \Exception("no Database");
        }
        return $this->db;
    }
}
