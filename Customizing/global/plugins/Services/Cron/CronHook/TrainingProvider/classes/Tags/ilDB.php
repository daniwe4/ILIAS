<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace CaT\Plugins\TrainingProvider\Tags;

/**
 * implementation for tag database handle
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilDB implements DB
{
    const TABLE_NAME = "tp_tags";
    const TABLE_ALLOCATION = "tp_tags_provider";

    /**
     * @var /*ilDBPdoMySQLInnoDB
     */
    protected $db = null;

    public function __construct(/*ilDBPdoMySQLInnoDB*/ $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function install()
    {
        $this->createTable();
        $this->createSequence();
    }

    /**
     * @inheritdoc
     */
    public function create($name, $color)
    {
        $next_id = $this->getNextId();
        $tag = new Tag($next_id, $name, $color);

        $values = array("id" => array("integer", $tag->getId())
                      , "name" => array("text", $tag->getName())
                      , "color" => array("text", $tag->getColorCode())
                    );

        $this->getDB()->insert(self::TABLE_NAME, $values);

        return $tag;
    }

    /**
     * @inheritdoc
     */
    public function select($id)
    {
        $query = "SELECT name, color\n"
                . " FROM " . self::TABLE_NAME . "\n"
                . " WHERE id = " . $this->getDB()->quote($id, "integer");

        $res = $this->getDB()->query($query);

        if ($this->getDB()->numRows($res) == 0) {
            throw new \Exception("No tag found for id: " . $id);
        }

        $row = $this->getDB()->fetchAssoc($res);

        return new Tag((int) $id, $row["name"], $row["color"]);
    }

    /**
     * @inheritdoc
     */
    public function update(\CaT\Plugins\TrainingProvider\Tags\Tag $tag)
    {
        $where = array("id" => array("integer", $tag->getId()));

        $values = array("name" => array("text", $tag->getName())
                      , "color" => array("text", $tag->getColorCode())
                    );

        $this->getDB()->update(self::TABLE_NAME, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function delete($id)
    {
        $this->deallocation($id);

        $query = "DELETE FROM " . self::TABLE_NAME . "\n"
                . " WHERE id = " . $this->getDB()->quote($id, "integer");

        $this->getDB()->manipulate($query);
    }

    /**
     * Allocate tag to provider
     *
     * @param int 													$id
     * @param int 													$provider_id
     */
    public function allocate($id, $provider_id)
    {
        $values = array("id" => array("integer", $id)
                      , "provider_id" => array("text", $provider_id)
                    );

        $this->getDB()->insert(self::TABLE_ALLOCATION, $values);
    }

    /**
     * Deallocate tag
     *
     * @param int 													$id
     */
    public function deallocation($id)
    {
        $query = "DELETE FROM " . self::TABLE_ALLOCATION . "\n"
                . " WHERE id = " . $this->getDB()->quote($id, "integer");

        $this->getDB()->manipulate($query);
    }

    /**
     * Deallocate tags by provider
     *
     * @param int 													$provider_id
     */
    public function deleteAllocationByProviderId($provider_id)
    {
        $query = "DELETE FROM " . self::TABLE_ALLOCATION . "\n"
                . " WHERE provider_id = " . $this->getDB()->quote($provider_id, "integer");

        $this->getDB()->manipulate($query);
    }

    /**
     * Get all allocated tags for provider
     *
     * @param int 													$provider_id
     *
     * @return Tag[] | []
     */
    public function getTagsFor($provider_id)
    {
        $query = "SELECT id, name, color\n"
                . " FROM " . self::TABLE_NAME . " tags\n"
                . " JOIN " . self::TABLE_ALLOCATION . " allo\n"
                . "     ON tags.id = allo.id\n"
                . " WHERE allo.provider_id = " . $this->getDB()->quote($provider_id, "integer");

        $res = $this->getDB()->query($query);

        if ($this->getDB()->numRows($res) == 0) {
            return array();
        }

        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $ret[] = new Tag((int) $row["id"], $row["name"], $row["color"]);
        }

        return $ret;
    }

    /**
     * Get all tags in raw format
     *
     * @return array<mixed[]>
     */
    public function getTagsRaw()
    {
        $query = "SELECT tag.id, tag.name, tag.color, COUNT(alloc.id) AS allocs\n"
                . " FROM " . self::TABLE_NAME . " tag\n"
                . " LEFT JOIN " . self::TABLE_ALLOCATION . " alloc\n"
                . "     ON tag.id = alloc.id"
                . " GROUP BY tag.id";

        $res = $this->getDB()->query($query);

        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $ret[] = $row;
        }

        return $ret;
    }

    /**
     * Get all assigned tags raw
     *
     * @return string[]
     */
    public function getAssignedTagsRaw()
    {
        $query = "SELECT tag.id, tag.name\n"
                . " FROM " . self::TABLE_NAME . " tag\n"
                . " JOIN " . self::TABLE_ALLOCATION . " alloc\n"
                . "     ON tag.id = alloc.id"
                . " GROUP BY tag.id";

        $res = $this->getDB()->query($query);

        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $ret[] = $row;
        }

        return $ret;
    }

    public function allocateTags($provider_id, array $tags)
    {
        assert('is_int($provider_id)');

        foreach ($tags as $key => $tag) {
            $this->allocate($tag, $provider_id);
        }
    }

    /**
     * Creates needed tables
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
                        'length' => 16,
                        'notnull' => true
                    ),
                    "color" => array(
                        'type' => 'text',
                        'length' => 7,
                        'notnull' => true
                    )
                );

            $this->getDB()->createTable(self::TABLE_NAME, $fields);
            $this->getDB()->addPrimaryKey(self::TABLE_NAME, array("id"));
        }

        if (!$this->getDB()->tableExists(self::TABLE_ALLOCATION)) {
            $fields = array(
                    "id" => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    "provider_id" => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    )
                );

            $this->getDB()->createTable(self::TABLE_ALLOCATION, $fields);
            $this->getDB()->addPrimaryKey(self::TABLE_ALLOCATION, array("id", "provider_id"));
        }
    }

    /**
     * Creates needed sequences
     */
    protected function createSequence()
    {
        if (!$this->getDB()->sequenceExists(self::TABLE_NAME)) {
            $this->getDB()->createSequence(self::TABLE_NAME);
        }
    }

    /**
     * Change length of tag name
     *
     * @return null
     */
    public function updateColumn1()
    {
        $field = array('type' => 'text', 'length' => 50, 'notnull' => true);
        $this->getDB()->modifyTableColumn(self::TABLE_NAME, "name", $field);
    }

    /**
     * Get the DB handler
     *
     * @return \ilDB
     */
    protected function getDB()
    {
        if ($this->db === null) {
            throw new \Exception("No databse defined in tag db implementation");
        }

        return $this->db;
    }

    /**
     * Get the next id for new provider
     *
     * @return int
     */
    protected function getNextId()
    {
        return (int) $this->getDB()->nextId(self::TABLE_NAME);
    }
}
