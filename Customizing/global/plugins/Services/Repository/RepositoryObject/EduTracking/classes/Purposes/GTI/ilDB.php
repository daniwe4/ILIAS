<?php

namespace CaT\Plugins\EduTracking\Purposes\GTI;

/**
 * Implementation of GTI db handling
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilDB implements DB
{
    const TABLE_NAME = "xetr_gti_data";

    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var \ilAppEventHandler
     */
    protected $evt_handler;

    public function __construct(\ilDBInterface $db, \ilAppEventHandler $evt_handler)
    {
        $this->db = $db;
        $this->evt_handler = $evt_handler;
    }

    /**
     * @inheritdoc
     */
    public function create(\ilObjEduTracking $obj)
    {
        $settings = new GTI($this, $this->evt_handler, $obj);

        $values = array("obj_id" => array("integer", $settings->getObjId()));
        $this->getDB()->insert(self::TABLE_NAME, $values);

        return $settings;
    }

    /**
     * @inheritdoc
     */
    public function selectFor(\ilObjEduTracking $obj)
    {
        $obj_id = $obj->getId();

        $query = "SELECT obj_id, category_id, set_trainingtime_manually, minutes" . PHP_EOL
                . " FROM " . self::TABLE_NAME . PHP_EOL
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, 'integer')
        ;

        $res = $this->getDB()->query($query);

        if ($this->getDB()->numRows($res) == 0) {
            throw new \Exception("No GTI settings found for: " . $obj_id);
        }

        $row = $this->getDB()->fetchAssoc($res);

        $cat_id = $row['category_id'];
        if (!is_null($cat_id)) {
            $cat_id = (int) $cat_id;
        }

        $minutes = $row['minutes'];
        if (is_null($minutes)) {
            $minutes = 0;
        }

        return new GTI($this, $this->evt_handler, $obj, $cat_id, $row['set_trainingtime_manually'], $minutes);
    }

    /**
     * @inheritdoc
     */
    public function update(GTI $settings)
    {
        $values = array(
            'category_id' => ['integer', $settings->getCategoryId()],
            'set_trainingtime_manually' => ['integer', (int) $settings->getSetTrainingTimeManually()],
            'minutes' => ['integer', $settings->getMinutes()]
        );

        $where = array(
            'obj_id' => ['integer', $settings->getObjId()]
        );

        $this->getDB()->update(self::TABLE_NAME, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function deleteFor(\ilObjEduTracking $obj)
    {
        $obj_id = $obj->getId();

        $query = "DELETE FROM " . self::TABLE_NAME . PHP_EOL
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");

        $this->getDB()->manipulate($query);
    }

    /**
     * Creates the table for config
     *
     * @return void
     */
    public function createTable()
    {
        if (!$this->getDB()->tableExists(self::TABLE_NAME)) {
            $fields = array(
                'obj_id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    )
            );

            $this->getDB()->createTable(self::TABLE_NAME, $fields);
        }
    }

    /**
     * Creates primary key
     *
     * @return void
     */
    public function createPrimaryKey()
    {
        $this->getDB()->addPrimaryKey(self::TABLE_NAME, array("obj_id"));
    }

    /**
     * Get the current db object
     *
     * @throws \Exception if no db is set
     *
     * @return \ilDBInterface
     */
    protected function getDB()
    {
        if (!$this->db) {
            throw new \Exception("no Database");
        }
        return $this->db;
    }

    public function update1()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "category_id")) {
            $this->getDB()->addTableColumn(
                self::TABLE_NAME,
                "category_id",
                array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => false
                )
            );
        }
    }

    public function update2()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "set_trainingtime_manually")) {
            $this->getDB()->addTableColumn(
                self::TABLE_NAME,
                "set_trainingtime_manually",
                array(
                    'type' => 'integer',
                    'length' => 1,
                    'notnull' => true
                )
            );
        }

        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "minutes")) {
            $this->getDB()->addTableColumn(
                self::TABLE_NAME,
                "minutes",
                array(
                    'type' => 'integer',
                    'length' => 8,
                    'notnull' => false
                )
            );
        }
    }
}
