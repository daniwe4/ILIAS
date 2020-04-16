<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

declare(strict_types=1);

namespace CaT\Plugins\Venues\Tags\Venue;

use  CaT\Plugins\Venues\Tags\Tag;

/**
 * implementation for tag database handle
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilDB implements DB
{
    const TABLE_NAME = "venues_tags";
    const TABLE_ALLOCATION = "venues_tags_venue";

    /**
     * @var \ilDB
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
    public function create(string $name, string $color) : Tag
    {
        $next_id = $this->getNextId();
        $tag = new Tag($next_id, $name, $color);

        $values = array("id" => array("integer", $tag->getId())
                      , "name" => array("text", $tag->getName())
                      , "color" => array("text", $tag->getColorCode())
                    );

        $this->db->insert(self::TABLE_NAME, $values);

        return $tag;
    }

    /**
     * @inheritdoc
     */
    public function select(int $id) : Tag
    {
        $query = "SELECT name, color\n"
                . " FROM " . self::TABLE_NAME . "\n"
                . " WHERE id = " . $this->db->quote($id, "integer");

        $res = $this->db->query($query);

        if ($this->db->numRows($res) == 0) {
            throw new \Exception("No tag found for id: " . $id);
        }

        $row = $this->db->fetchAssoc($res);

        return new Tag((int) $id, $row["name"], $row["color"]);
    }

    /**
     * @inheritdoc
     */
    public function selectForIds(array $ids) : array
    {
        $query = "SELECT id, name, color\n"
            . " FROM " . self::TABLE_NAME . "\n"
            . " WHERE " . $this->db->in("id", $ids, false, "integer");

        $res = $this->db->query($query);

        $ret = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $ret[] = new Tag((int) $row["id"], $row["name"], $row["color"]);
        }

        return $ret;
    }

    /**
     * @inheritdoc
     */
    public function update(Tag $tag)
    {
        $where = array("id" => array("integer", $tag->getId()));

        $values = array("name" => array("text", $tag->getName())
                      , "color" => array("text", $tag->getColorCode())
                    );

        $this->db->update(self::TABLE_NAME, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function delete(int $id)
    {
        assert('is_int($id)');
        $this->deallocation($id);

        $query = "DELETE FROM " . self::TABLE_NAME . "\n"
                . " WHERE id = " . $this->db->quote($id, "integer");

        $this->db->manipulate($query);
    }

    /**
     * @inheritdoc
     */
    public function allocate(int $id, int $venue_id)
    {
        $values = array("id" => array("integer", $id)
                      , "venue_id" => array("text", $venue_id)
                    );

        $this->db->insert(self::TABLE_ALLOCATION, $values);
    }

    /**
     * @inheritdoc
     */
    public function deallocation(int $id)
    {
        $query = "DELETE FROM " . self::TABLE_ALLOCATION . "\n"
                . " WHERE id = " . $this->db->quote($id, "integer");

        $this->db->manipulate($query);
    }

    public function deleteAllocationByVenueId(int $venue_id)
    {
        $query = "DELETE FROM " . self::TABLE_ALLOCATION . "\n"
                . " WHERE venue_id = " . $this->db->quote($venue_id, "integer");

        $this->db->manipulate($query);
    }

    public function getTagsFor(int $venue_id) : array
    {
        $query = "SELECT id, name, color\n"
                . " FROM " . self::TABLE_NAME . " tags\n"
                . " JOIN " . self::TABLE_ALLOCATION . " allo\n"
                . "     ON tags.id = allo.id\n"
                . " WHERE allo.venue_id = " . $this->db->quote($venue_id, "integer");

        $res = $this->db->query($query);

        if ($this->db->numRows($res) == 0) {
            return array();
        }

        $ret = array();
        while ($row = $this->db->fetchAssoc($res)) {
            $ret[] = new Tag((int) $row["id"], $row["name"], $row["color"]);
        }

        return $ret;
    }

    public function getTagsRaw() : array
    {
        $query = "SELECT tag.id, tag.name, tag.color, COUNT(alloc.id) AS allocs\n"
                . " FROM " . self::TABLE_NAME . " tag\n"
                . " LEFT JOIN " . self::TABLE_ALLOCATION . " alloc\n"
                . "     ON tag.id = alloc.id"
                . " GROUP BY tag.id";

        $res = $this->db->query($query);

        $ret = array();
        while ($row = $this->db->fetchAssoc($res)) {
            $ret[] = $row;
        }

        return $ret;
    }

    public function getAssignedTagsRaw() : array
    {
        $query = "SELECT tag.id, tag.name\n"
                . " FROM " . self::TABLE_NAME . " tag\n"
                . " JOIN " . self::TABLE_ALLOCATION . " alloc\n"
                . "     ON tag.id = alloc.id"
                . " GROUP BY tag.id";

        $res = $this->db->query($query);

        $ret = array();
        while ($row = $this->db->fetchAssoc($res)) {
            $ret[] = $row;
        }

        return $ret;
    }

    public function allocateTags(int $venue_id, array $tags)
    {
        foreach ($tags as $key => $tag) {
            $this->allocate($tag->getId(), $venue_id);
        }
    }

    protected function createTable()
    {
        if (!$this->db->tableExists(self::TABLE_NAME)) {
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

            $this->db->createTable(self::TABLE_NAME, $fields);
            $this->db->addPrimaryKey(self::TABLE_NAME, array("id"));
        }

        if (!$this->db->tableExists(self::TABLE_ALLOCATION)) {
            $fields = array(
                    "id" => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    "venue_id" => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    )
                );

            $this->db->createTable(self::TABLE_ALLOCATION, $fields);
            $this->db->addPrimaryKey(self::TABLE_ALLOCATION, array("id", "venue_id"));
            $this->db->createSequence(self::TABLE_NAME);
        }
    }

    public function updateColumn1()
    {
        $field = array('type' => 'text', 'length' => 50, 'notnull' => true);
        $this->db->modifyTableColumn(self::TABLE_NAME, "name", $field);
    }

    protected function getNextId() : int
    {
        return (int) $this->db->nextId(self::TABLE_NAME);
    }
}
