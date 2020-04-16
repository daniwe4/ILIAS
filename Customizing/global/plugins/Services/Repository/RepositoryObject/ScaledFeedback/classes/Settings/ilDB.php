<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\ScaledFeedback\Settings;

class ilDB implements DB
{
    const TABLENAME_SETS = "xfbk_sets";
    const TABLENAME = "xfbk_settings";

    /**
     * @var \ilDBInterface
     */
    private $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function install()
    {
        $this->createTable();
    }

    /**
     * @inheritdoc
     */
    public function create(int $obj_id) : Settings
    {
        $settings = new Settings($obj_id, 0, false, 0);

        $values = array(
            'obj_id' => ['integer', $settings->getObjId()],
            'set_id' => ['integer', $settings->getSetId()],
            'is_online' => ['integer', $settings->getOnline()],
            'lp_mode' => ['integer', $settings->getLPMode()]
            );
        $this->getDB()->insert(self::TABLENAME, $values);

        return $settings;
    }

    /**
     * @inheritdoc
     */
    public function update(Settings $settings)
    {
        $obj_id = $settings->getObjId();
        $where = ["obj_id" => ["integer", $obj_id]];
        $values = array(
            'set_id' => ['integer', $settings->getSetId()],
            'is_online' => ['integer', $settings->getOnline()],
            'lp_mode' => ['integer', $settings->getLPMode()]
            );
        $this->getDB()->update(self::TABLENAME, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function selectAll() : array
    {
        $settings = array();

        $query = "SELECT" . PHP_EOL
                . "    obj_id," . PHP_EOL
                . "    set_id," . PHP_EOL
                . "    is_online," . PHP_EOL
                . "    lp_mode" . PHP_EOL
                . "FROM " . self::TABLENAME . PHP_EOL
                . "ORDER BY obj_id ASC";
        $result = $this->getDB()->query($query);

        while ($row = $this->getDB()->fetchAssoc($result)) {
            $settings[] = $this->getSettingsObject($row);
        }

        return $settings;
    }

    /**
     * @inheritdoc
     */
    public function selectById(int $id) : Settings
    {
        $where = "WHERE obj_id = " . $this->getDB()->quote($id, "integer");
        $query = "SELECT" . PHP_EOL
                . "    obj_id," . PHP_EOL
                . "    set_id," . PHP_EOL
                . "    is_online," . PHP_EOL
                . "    lp_mode" . PHP_EOL
                . "FROM " . self::TABLENAME . "" . PHP_EOL;
        $result = $this->getDB()->query($query . $where);

        if ($this->getDB()->numRows($result) == 0) {
            throw new \LogicException(__METHOD__ . " no Settings found for obj_id " . $id);
        }

        return $this->getSettingsObject($this->getDB()->fetchAssoc($result));
    }

    /**
     * @inheritdoc
     */
    public function delete(int $id)
    {
        $query = "DELETE FROM " . self::TABLENAME . PHP_EOL
                . "WHERE obj_id = " . $this->getDB()->quote($id, "integer");
        $this->getDB()->manipulate($query);
    }

    /**
     * Create a new Settings object.
     *
     * @param 	array 	$row
     * @return 	Settings
     */
    protected function getSettingsObject(array $row) : Settings
    {
        return new Settings(
            (int) $row['obj_id'],
            (int) $row['set_id'],
            (bool) $row['is_online'],
            (int) $row['lp_mode']
        );
    }

    /**
     * Install the base table structure for feedback.
     *
     * @return 	void
     */
    protected function createTable()
    {
        if (!$this->getDB()->tableExists(self::TABLENAME)) {
            $fields = array(
                'obj_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                    ),
                'set_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                    ),
                'is_online' => array(
                    'type' => 'integer',
                    'length' => 1
                    ),
                'lp_mode' => array(
                    'type' => 'integer',
                    'length' => 4
                    )
                );

            $this->getDB()->createTable(self::TABLENAME, $fields);
        }
    }

    /**
     * Create primary key for table xfbk_settings.
     */
    public function createPrimaryKeyForSettings()
    {
        $this->createPrimaryKey(self::TABLENAME, array("obj_id"));
    }

    /**
     * Create sequence for table xfbk_settings.
     */
    public function createSequenceForSettings()
    {
        $this->getDB()->createSequence(self::TABLENAME);
    }

    protected function createPrimaryKey(string $tablename, array $primary_keys)
    {
        $this->getDB()->addPrimaryKey($tablename, $primary_keys);
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
}
