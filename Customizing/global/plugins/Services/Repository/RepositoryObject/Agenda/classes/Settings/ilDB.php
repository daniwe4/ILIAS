<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\Agenda\Settings;

use DateTime;
use DateTimeZone;
use ilDBInterface;

class ilDB implements DB
{
    const TABLE_SETTINGS = "xage_settings";

    /**
     * @var ilDBInterface
     */
    protected $db;

    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function create(int $obj_id) : Settings
    {
        $settings = new Settings($obj_id);

        $values = array(
            'obj_id' => ['integer', $settings->getObjId()]
        );

        $this->getDB()->insert(self::TABLE_SETTINGS, $values);

        return $settings;
    }

    /**
     * @inheritdoc
     */
    public function update(Settings $settings)
    {
        $start_time = null;
        if ($settings->getStartTime() !== null) {
            $start_time = $settings->getStartTime()->format("H:i");
        }
        $obj_id = $settings->getObjId();
        $where = ['obj_id' => ['integer', $obj_id]];
        $values = array(
            'start_time' => ['text', $start_time],
        );

        $this->getDB()->update(self::TABLE_SETTINGS, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function selectFor(int $obj_id) : Settings
    {
        $query = "SELECT" . PHP_EOL
                 . "    obj_id," . PHP_EOL
                 . "    start_time" . PHP_EOL
                 . "FROM " . self::TABLE_SETTINGS . PHP_EOL
                 . "WHERE" . PHP_EOL
                 . "    obj_id = " . $this->getDB()->quote($obj_id, 'integer') . PHP_EOL
        ;
        $result = $this->getDB()->query($query);

        if ($this->getDB()->numRows($result) == 0) {
            return $this->create($obj_id);
        }

        return $this->createSettingsObject($this->getDB()->fetchAssoc($result));
    }

    /**
     * @inheritdoc
     */
    public function deleteFor(int $obj_id)
    {
        $query = "DELETE FROM " . self::TABLE_SETTINGS . PHP_EOL
                 . "WHERE" . PHP_EOL
                 . "    obj_id = " . $this->getDB()->quote($obj_id, 'integer') . PHP_EOL
        ;

        $this->getDB()->manipulate($query);
    }

    /**
     * Create an Settings object from an assoziative array.
     */
    public function createSettingsObject(array $row) : Settings
    {
        $start_time = "00:00";
        if (!is_null($row["start_time"])) {
            $start_time = $row["start_time"];
        }

        return new Settings(
            (int) $row['obj_id'],
            new DateTime($start_time, new DateTimeZone("Europe/Berlin"))
        );
    }

    /**
     * Create the table for settings.
     *
     * @return 	void
     */
    public function createTable()
    {
        if (!$this->getDB()->tableExists(self::TABLE_SETTINGS)) {
            $fields = array(
                'obj_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'start_time' => array(
                    'type' => 'text',
                    'length' => 19,
                    'notnull' => false,
                    'default' => '00:00'
                )
            );

            $this->getDB()->createTable(self::TABLE_SETTINGS, $fields);
        }
    }

    /**
     * Create a primary key for table
     *
     * @return 	void
     */
    public function createPrimaryKey()
    {
        $this->getDB()->addPrimaryKey(self::TABLE_SETTINGS, array('obj_id'));
    }

    /**
     * Get instance of db
     *
     * @return 	ilDBInterface
     */
    private function getDB()
    {
        if (!$this->db) {
            throw new \Exception("no database");
        }

        return $this->db;
    }
}
