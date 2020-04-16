<?php

declare(strict_types=1);

namespace CaT\Plugins\RoomSetup\ServiceOptions;

/**
 * ILIAS implementation of db interface for service optins
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilDB implements DB
{
    const TABLE_NAME = "xrse_service_options";
    const TABLE_EQUIPMENT_SO = "xrse_equipment_so";

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
    public function install()
    {
        $this->createTable();
    }

    /**
     * @inheritdoc
     */
    public function create(string $name, bool $active) : ServiceOption
    {
        $next_id = $this->getNextId();
        $service_option = new ServiceOption($next_id, $name, $active);

        $values = array("id" => array("integer", $service_option->getId())
                      , "name" => array("text", $service_option->getName())
                      , "is_active" => array("integer", $service_option->getActive())
            );

        $this->getDB()->insert(self::TABLE_NAME, $values);

        return $service_option;
    }

    /**
     * @inheritdoc
     */
    public function update(ServiceOption $service_option)
    {
        $where = array("id" => array("integer", $service_option->getId()));

        $values = array("name" => array("text", $service_option->getName()),
                        "is_active" => array("integer", $service_option->getActive())
                );

        $this->getDB()->update(self::TABLE_NAME, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function select(int $id) : ServiceOption
    {
        $query = "SELECT id, name, is_active\n"
                . " FROM " . self::TABLE_NAME . "\n"
                . " WHERE id = " . $this->getDB()->quote($id, "integer");

        $res = $this->getDB()->query($query);

        if ($this->getDB()->numRows($res) == 0) {
            throw new \LogicException(__METHOD__ . " no data found for id " . $id);
        }

        $row = $this->getDB()->fetchAssoc($res);

        return new ServiceOption((int) $row["id"], $row["name"], (bool) $row["is_active"]);
    }

    /**
     * @inheritdoc
     */
    public function selectAll(int $offset, int $limit, string $order_field, string $order_direction) : array
    {
        $query = "SELECT id, name, is_active" . PHP_EOL
                . " FROM " . self::TABLE_NAME . PHP_EOL
                . " ORDER BY " . $order_field . " " . $order_direction . PHP_EOL
                . " LIMIT " . $limit . " OFFSET " . $offset;

        $res = $this->getDB()->query($query);
        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $ret[] = new ServiceOption((int) $row["id"], $row["name"], (bool) $row["is_active"]);
        }

        return $ret;
    }

    /**
     * @inheritdoc
     */
    public function selectAllCount($only_active = false)
    {
        $query = "SELECT id" . PHP_EOL
                . " FROM " . self::TABLE_NAME . PHP_EOL;

        if ($only_active) {
            $query .= " WHERE is_active = 1";
        }

        $res = $this->getDB()->query($query);
        return $this->getDB()->numRows($res);
    }

    /**
     * @inheritdoc
     */
    public function selectAllActive() : array
    {
        $query = "SELECT id, name, is_active" . PHP_EOL
                . " FROM " . self::TABLE_NAME . PHP_EOL
                . " WHERE is_active = 1" . PHP_EOL;

        $res = $this->getDB()->query($query);
        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $ret[] = new ServiceOption((int) $row["id"], $row["name"], (bool) $row["is_active"]);
        }

        return $ret;
    }

    /**
     * @inheritdoc
     */
    public function getMissingAssignedInactiveOptions(array $missing) : array
    {
        $query = "SELECT id, name, is_active\n"
                . " FROM " . self::TABLE_NAME . "\n"
                . " WHERE " . $this->getDB()->in("id", $missing, false, "integer");

        $res = $this->getDB()->query($query);
        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $ret[] = new ServiceOption((int) $row["id"], $row["name"], (bool) $row["is_active"]);
        }

        return $ret;
    }

    /**
     * @inheritdoc
     */
    public function deleteById(int $id)
    {
        assert('is_int($id)');

        $this->deleteFromAllocations($id);

        $query = "DELETE FROM " . self::TABLE_NAME . "\n"
                . " WHERE id = " . $this->getDB()->quote($id, "integer");

        $this->getDB()->manipulate($query);
    }

    /**
     * Delete service option from allocations
     *
     * @param int 	$id
     *
     * @return null
     */
    protected function deleteFromAllocations($id)
    {
        assert('is_int($id)');
        $query = "DELETE FROM " . self::TABLE_EQUIPMENT_SO . "\n"
                . " WHERE service_option_id = " . $this->getDB()->quote($id);

        $this->getDB()->manipulate($query);
    }

    /**
     * Create table for service options
     *
     * @return null
     */
    protected function createTable()
    {
        if (!$this->getDB()->tableExists(self::TABLE_NAME)) {
            $fields = array(
                    "id" => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    "name" => array(
                        'type' => 'text',
                        'length' => 255,
                        'notnull' => true
                    )
                );

            $this->getDB()->createTable(self::TABLE_NAME, $fields);
            $this->getDB()->addPrimaryKey(self::TABLE_NAME, array("id"));
            $this->getDB()->createSequence(self::TABLE_NAME);
        }
    }

    /**
     * Update step 1 for tables
     *
     * @return null
     */
    public function updateTable1()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "is_active")) {
            $field = array(
                        'type' => 'integer',
                        'length' => 1
            );

            $this->getDB()->addTableColumn(self::TABLE_NAME, "is_active", $field);
        }
    }

    /**
     * Get the DB handler
     *
     * @return \ilDB
     */
    protected function getDB()
    {
        if ($this->db === null) {
            throw new \Exception("No databse defined in service option db implementation");
        }

        return $this->db;
    }

    /**
     * Get the next id for new venue
     *
     * @return int
     */
    protected function getNextId()
    {
        return (int) $this->getDB()->nextId(self::TABLE_NAME);
    }
}
