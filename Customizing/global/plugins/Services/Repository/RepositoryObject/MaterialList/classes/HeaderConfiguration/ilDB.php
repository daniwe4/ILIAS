<?php

namespace CaT\Plugins\MaterialList\HeaderConfiguration;

/**
 * ILIAS implementation of the interface for configuration entries db actions
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilDB implements DB
{
    const TABLE_NAME = "xmat_header";

    /**
     * @var \ilDB
     */
    protected $db;

    public function __construct($db)
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
    public function update(ConfigurationEntry $configuration_entry)
    {
        $where = array("id" => array("integer", $configuration_entry->getId()));

        $values = array("source_for_value" => array("text", $configuration_entry->getSourceForValue()));

        $this->getDB()->update(self::TABLE_NAME, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function create(string $type, $source_for_value)
    {
        assert(is_string($source_for_value) || is_int($source_for_value));

        $next_id = $this->getNextId();
        $configuration_entry = new ConfigurationEntry($next_id, $type, $source_for_value);

        $values = array( "id" => array("integer", $configuration_entry->getId())
                , "type" => array("text", $configuration_entry->getType())
                , "source_for_value" => array("text", $configuration_entry->getSourceForValue())
                );
        $this->getDB()->insert(self::TABLE_NAME, $values);

        return $configuration_entry;
    }

    /**
     * @inheritdoc
     */
    public function selectAll()
    {
        $query = "SELECT id, type, source_for_value\n"
                . " FROM " . self::TABLE_NAME . "\n";

        $res = $this->getDB()->query($query);

        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $ret[] = new ConfigurationEntry((int) $row["id"], $row["type"], $row["source_for_value"]);
        }

        return $ret;
    }

    /**
     * @inheritdoc
     */
    public function delete(int $id)
    {
        $query = "DELETE FROM " . self::TABLE_NAME . "\n"
                . " WHERE id = " . $this->getDB()->quote($id, "integer");

        $this->getDB()->manipulate($query);
    }

    /**
     * Create table for configuration entries
     *
     * @return null
     */
    public function createTable()
    {
        if (!$this->getDB()->tableExists(self::TABLE_NAME)) {
            $fields =
                array('id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'type' => array(
                        'type' => 'text',
                        'length' => 50,
                        'notnull' => true
                    )					,
                    'source_for_value' => array(
                        'type' => 'text',
                        'length' => 100,
                        'notnull' => true
                    )
                );

            $this->getDB()->createTable(self::TABLE_NAME, $fields);
            $this->getDB()->addPrimaryKey(self::TABLE_NAME, array("id"));
            $this->getDB()->createSequence(self::TABLE_NAME);
        }
    }

    /**
     * Get next db id
     *
     * @return int
     */
    public function getNextId()
    {
        return (int) $this->getDB()->nextId(self::TABLE_NAME);
    }

    /**
     * Get the db handler
     *
     * @throws \Exception
     *
     * @return \ilDB
     */
    protected function getDB()
    {
        if (!$this->db) {
            throw new \Exception("no Database");
        }
        return $this->db;
    }
}
