<?php

namespace CaT\Plugins\CourseClassification\Options;

abstract class ilDB implements DB
{
    /**
     * @var \ilDBInterface
     */
    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function install()
    {
        $this->createTable();
    }

    public function configurePrimaryKeys()
    {
        $this->getDB()->addPrimaryKey(static::TABLE_NAME, array("id"));
    }

    public function createSequence()
    {
        $this->getDB()->createSequence(static::TABLE_NAME);
    }

    /**
     * @inheritdoc
     */
    public function create(string $caption) : Option
    {
        $id = $this->getNextId();
        $option = new Option($id, $caption);

        $values = array("id" => array("integer", $option->getId())
            , "caption" => array("text", $option->getCaption())
        );

        $this->getDB()->insert(static::TABLE_NAME, $values);

        return $option;
    }

    /**
     * @inheritdoc
     */
    public function update(Option $option)
    {
        $where = array("id" => array("integer", $option->getId()));

        $values = array("caption" => array("text", $option->getCaption()));

        $this->getDB()->update(static::TABLE_NAME, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function select() : array
    {
        $query = "SELECT id, caption\n"
                . " FROM " . static::TABLE_NAME . "\n";

        $result = $this->getDB()->query($query);
        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($result)) {
            $option = new Option((int) $row["id"], $row["caption"]);
            $ret[] = $option;
        }

        return $ret;
    }

    /**
     * @inheritdoc
     */
    public function delete(int $id)
    {
        $query = "DELETE FROM " . static::TABLE_NAME . "\n"
                . " WHERE id = " . $this->getDB()->quote($id, "integer");

        $this->getDB()->manipulate($query);
    }

    /**
     * Create table for options
     *
     * @return null
     */
    protected function createTable()
    {
        if (!$this->getDB()->tableExists(static::TABLE_NAME)) {
            $fields =
                array('id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'caption' => array(
                        'type' => 'text',
                        'length' => 128,
                        'notnull' => true
                    )
                );

            $this->getDB()->createTable(static::TABLE_NAME, $fields);
        }
    }

    /**
     * Get intance of db
     *
     * @throws \Exceptio
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
