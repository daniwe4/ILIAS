<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

declare(strict_types=1);

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
     * @var \ilDBInterface
     */
    protected $db = null;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function install() : void
    {
        $this->createTable();
        $this->createSequence();
    }

    /**
     * @inheritdoc
     */
    public function create(string $name, string $color) : Tag
    {
        $next_id = $this->getNextId();
        $tag = new Tag($next_id, $name, $color);

        $values = [
            "id" => ["integer", $tag->getId()],
            "name" => ["text", $tag->getName()],
            "color" => ["text", $tag->getColorCode()]
        ];

        $this->getDB()->insert(self::TABLE_NAME, $values);

        return $tag;
    }

    /**
     * @inheritdoc
     */
    public function select(int $id) : Tag
    {
        $query =
             "SELECT name, color" . PHP_EOL
            . "FROM " . self::TABLE_NAME . PHP_EOL
            . "WHERE id = " . $this->getDB()->quote($id, "integer") . PHP_EOL
        ;

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
    public function update(Tag $tag) : void
    {
        $where = ["id" => ["integer", $tag->getId()]];

        $values = [
            "name" => ["text", $tag->getName()],
            "color" => ["text", $tag->getColorCode()]
        ];

        $this->getDB()->update(self::TABLE_NAME, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function delete(int $id) : void
    {
        $this->deallocation($id);

        $query =
             "DELETE FROM " . self::TABLE_NAME . PHP_EOL
            . "WHERE id = " . $this->getDB()->quote($id, "integer") . PHP_EOL
        ;

        $this->getDB()->manipulate($query);
    }

    /**
     * Allocate tag to provider
     */
    public function allocate(int $id, int $provider_id) : void
    {
        $values = [
            "id" => ["integer", $id],
            "provider_id" => ["integer", $provider_id]
        ];

        $this->getDB()->insert(self::TABLE_ALLOCATION, $values);
    }

    public function deallocation(int $id) : void
    {
        $query =
             "DELETE FROM " . self::TABLE_ALLOCATION . PHP_EOL
            . "WHERE id = " . $this->getDB()->quote($id, "integer") . PHP_EOL
        ;

        $this->getDB()->manipulate($query);
    }

    public function deleteAllocationByProviderId(int $provider_id) : void
    {
        $query =
             "DELETE FROM " . self::TABLE_ALLOCATION . PHP_EOL
            . "WHERE provider_id = " . $this->getDB()->quote($provider_id, "integer") . PHP_EOL
        ;

        $this->getDB()->manipulate($query);
    }

    /**
     * Get all allocated tags for provider
     *
     * @return Tag[] | []
     */
    public function getTagsFor(int $provider_id) : array
    {
        $query =
             "SELECT id, name, color" . PHP_EOL
            . "FROM " . self::TABLE_NAME . " tags" . PHP_EOL
            . "JOIN " . self::TABLE_ALLOCATION . " allo" . PHP_EOL
            . "    ON tags.id = allo.id" . PHP_EOL
            . "WHERE allo.provider_id = " . $this->getDB()->quote($provider_id, "integer") . PHP_EOL
        ;

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
    public function getTagsRaw() : array
    {
        $query =
             "SELECT tag.id, tag.name, tag.color, COUNT(alloc.id) AS allocs" . PHP_EOL
            . "FROM " . self::TABLE_NAME . " tag" . PHP_EOL
            . "LEFT JOIN " . self::TABLE_ALLOCATION . " alloc" . PHP_EOL
            . "    ON tag.id = alloc.id" . PHP_EOL
            . "GROUP BY tag.id" . PHP_EOL
        ;

        $res = $this->getDB()->query($query);

        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $ret[] = $row;
        }

        return $ret;
    }

    public function getAssignedTagsRaw() : array
    {
        $query =
             "SELECT tag.id, tag.name" . PHP_EOL
            . "FROM " . self::TABLE_NAME . " tag" . PHP_EOL
            . "JOIN " . self::TABLE_ALLOCATION . " alloc" . PHP_EOL
            . "    ON tag.id = alloc.id" . PHP_EOL
            . "GROUP BY tag.id" . PHP_EOL
        ;

        $res = $this->getDB()->query($query);

        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $ret[] = $row;
        }

        return $ret;
    }

    public function allocateTags(int $provider_id, array $tags)
    {
        foreach ($tags as $key => $tag) {
            $this->allocate($tag, $provider_id);
        }
    }

    /**
     * Creates needed tables
     */
    protected function createTable() : void
    {
        if (!$this->getDB()->tableExists(self::TABLE_NAME)) {
            $fields = [
                    "id" => [
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ],
                    "name" => [
                        'type' => 'text',
                        'length' => 16,
                        'notnull' => true
                    ],
                    "color" => [
                        'type' => 'text',
                        'length' => 7,
                        'notnull' => true
                    ]
                ];

            $this->getDB()->createTable(self::TABLE_NAME, $fields);
            $this->getDB()->addPrimaryKey(self::TABLE_NAME, array("id"));
        }

        if (!$this->getDB()->tableExists(self::TABLE_ALLOCATION)) {
            $fields = [
                    "id" => [
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ],
                    "provider_id" => [
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ]
                ];

            $this->getDB()->createTable(self::TABLE_ALLOCATION, $fields);
            $this->getDB()->addPrimaryKey(self::TABLE_ALLOCATION, array("id", "provider_id"));
        }
    }

    /**
     * Creates needed sequences
     */
    protected function createSequence() : void
    {
        if (!$this->getDB()->sequenceExists(self::TABLE_NAME)) {
            $this->getDB()->createSequence(self::TABLE_NAME);
        }
    }

    public function updateColumn1() : void
    {
        $field = ['type' => 'text', 'length' => 50, 'notnull' => true];
        $this->getDB()->modifyTableColumn(self::TABLE_NAME, "name", $field);
    }

    protected function getDB() : \ilDBInterface
    {
        if ($this->db === null) {
            throw new \Exception("No databse defined in tag db implementation");
        }

        return $this->db;
    }

    protected function getNextId() : int
    {
        return (int) $this->getDB()->nextId(self::TABLE_NAME);
    }
}
