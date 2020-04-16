<?php

namespace CaT\Plugins\EduTracking\Purposes\IDD;

/**
 * Implementation of IDD db handling
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilDB implements DB
{
    const TABLE_NAME = "xetr_idd_data";

    /**
     * @var \ilDBInterface
     */
    protected $db;

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
        $settings = new IDD($this, $this->evt_handler, $obj);

        $values = [
            "obj_id" => ["integer", $settings->getObjId()],
            "minutes" => ["integer", $settings->getMinutes()]
        ];
        $this->getDB()->insert(self::TABLE_NAME, $values);

        return $settings;
    }

    /**
     * @inheritdoc
     */
    public function selectFor(\ilObjEduTracking $obj)
    {
        $obj_id = $obj->getId();
        $query = "SELECT obj_id, minutes" . PHP_EOL
                . "FROM " . self::TABLE_NAME . PHP_EOL
                . "WHERE obj_id = " . $this->getDB()->quote($obj_id, 'integer') . PHP_EOL
        ;

        $res = $this->getDB()->query($query);

        if ($this->getDB()->numRows($res) == 0) {
            throw new \Exception("No IDD settings found for: " . $obj_id);
        }

        $row = $this->getDB()->fetchAssoc($res);
        $minutes = $row["minutes"];
        if ($minutes !== null) {
            $minutes = (int) $minutes;
        }
        return new IDD($this, $this->evt_handler, $obj, $minutes);
    }

    /**
     * @inheritdoc
     */
    public function update(IDD $settings)
    {
        $where = array("obj_id" => array("integer", $settings->getObjId()));
        $values = array("minutes" => array("integer", $settings->getMinutes()));

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
                    ),
                'minutes' => array(
                        'type' => 'integer',
                        'length' => 8,
                        'notnull' => false
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
        $this->getDB()->addPrimaryKey(static::TABLE_NAME, array("obj_id"));
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
}
