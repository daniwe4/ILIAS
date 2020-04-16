<?php

namespace CaT\Plugins\EduTracking\Purposes\IDD\Configuration;

/**
 * Implementation for ILIAS of IDD DB interface
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilDB implements DB
{
    const TABLE_NAME = "xetr_idd_config";

    /**
     * @var \ilDBInterface
     */
    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }
    /**
     * @inheritdoc
     */
    public function select()
    {
        $query = "SELECT id, available" . PHP_EOL
                . "FROM " . self::TABLE_NAME . PHP_EOL
                . "ORDER BY id DESC" . PHP_EOL
                . "LIMIT 1" . PHP_EOL
        ;

        $res = $this->getDB()->query($query);
        if ($this->getDB()->numRows($res) == 0) {
            return null;
        }

        $row = $this->getDB()->fetchAssoc($res);

        return new ConfigIDD((int) $row["id"], (bool) $row["available"]);
    }

    /**
     * Creates a new configuration entry
     *
     * @param bool 	$available
     * @param int 	$changed_by
     *
     * @return void
     */
    public function insert($available, $changed_by)
    {
        $next_id = $this->getNextId();
        $today = date("Y-m-d H:i:s");

        $values = array(
            "id" => array("integer", $next_id),
            "available" => array("integer", $available),
            "changed_by" => array("text", $changed_by),
            "changed_at" => array("text", $today)
        );

        $this->getDB()->insert(self::TABLE_NAME, $values);
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
                'id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                'available' => array(
                        'type' => 'integer',
                        'length' => 1,
                        'notnull' => true
                    ),
                'changed_by' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                'changed_at' => array(
                        'type' => 'text',
                        'length' => 25,
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
        $this->getDB()->addPrimaryKey(static::TABLE_NAME, array("id"));
    }

    /**
     * Create the sequence for table
     *
     * @return void
     */
    public function createSequence()
    {
        $this->getDB()->createSequence(static::TABLE_NAME);
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

    /**
     * Get next id for option entry
     */
    public function getNextId()
    {
        return (int) $this->getDB()->nextId(static::TABLE_NAME);
    }
}
