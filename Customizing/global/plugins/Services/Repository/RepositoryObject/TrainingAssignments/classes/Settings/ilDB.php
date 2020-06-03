<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainingAssignments\Settings;

class ilDB implements DB
{
    const TABLE_NAME = "xatr_settings";

    /**
     * @var \ilDBInterface
     */
    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function createAssignmentSettings(int $obj_id, bool $show_info_tab = false)
    {
        $settings = new AssignmentSettings($obj_id, $show_info_tab);

        $values = [
            'obj_id' => ['integer', $settings->getObjId()],
            'show_info_tab' => ['integer', $settings->getShowInfoTab()]
        ];

        $this->db->insert(self::TABLE_NAME, $values);

        return $settings;
    }

    public function selectByObjId(int $obj_id) : AssignmentSettings
    {
        $sql =
             'SELECT obj_id, show_info_tab' . PHP_EOL
            . 'FROM ' . self::TABLE_NAME . PHP_EOL
            . 'WHERE obj_id = ' . $this->db->quote($obj_id, 'integer') . PHP_EOL
        ;

        $result = $this->db->query($sql);

        if ($this->db->numRows($result) == 0) {
            return new AssignmentSettings($obj_id);
        }

        return $this->getAssignmentObject($this->db->fetchAssoc($result));
    }


    public function updateAssignmentsSettings(AssignmentSettings $settings)
    {
        $obj_id = $settings->getObjId();
        $where = ['obj_id' => ['integer', $obj_id]];
        $values = ['show_info_tab' => ['integer', (int) $settings->getShowInfoTab()]];
        $this->db->replace(self::TABLE_NAME, $where, $values);
    }

    public function delete(int $obj_id)
    {
        $q = "DELETE FROM " . self::TABLE_NAME . PHP_EOL
            . " WHERE obj_id = " . $this->db->quote($obj_id, "integer");

        $this->db->manipulate($q);
    }

    protected function getAssignmentObject(array $row)
    {
        return new AssignmentSettings(
            $row['obj_id'],
            $row['show_info_tab']
        );
    }

    public function createTable()
    {
        if (!$this->db->tableExists(self::TABLE_NAME)) {
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

            $this->db->createTable(self::TABLE_NAME, $fields);
        }
    }

    public function createPrimaryKey()
    {
        $this->db->addPrimaryKey(self::TABLE_NAME, ['obj_id']);
    }
}
