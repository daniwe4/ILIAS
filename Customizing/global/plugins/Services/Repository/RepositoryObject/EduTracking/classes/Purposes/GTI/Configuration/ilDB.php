<?php

namespace CaT\Plugins\EduTracking\Purposes\GTI\Configuration;

/**
 * Implementation for ILIAS of GTI DB interface
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilDB implements DB
{
    const TABLE_NAME = "xetr_gti_config";
    const TABLE_NAME_CATEGORIES = "xetr_gti_categories";

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

        return new ConfigGTI((int) $row["id"], (bool) $row["available"]);
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
     * Select all categories.
     *
     * @return CategorieGTI[]
     */
    public function selectCategories() : array
    {
        $query =
             "SELECT id, name" . PHP_EOL
            . "FROM " . self::TABLE_NAME_CATEGORIES . PHP_EOL
        ;

        $result = $this->getDB()->query($query);

        $categories = array();
        while ($row = $this->getDB()->fetchAssoc($result)) {
            $categories[] = new CategoryGTI($row['id'], $row['name']);
        }

        return $categories;
    }

    public function getTitleById(int $category_id) : string
    {
        $query =
            "SELECT name" . PHP_EOL
            . "FROM " . self::TABLE_NAME_CATEGORIES . PHP_EOL
            . "WHERE id = " . $this->db->quote($category_id, "integer") . PHP_EOL
        ;

        $result = $this->getDB()->query($query);
        $row = $this->getDB()->fetchAssoc($result);
        return $row["name"];
    }

    /**
     * @param CategoryGTI[] $categories
     */
    public function insertCategories(array $categories, int $changed_by)
    {
        $today = date("Y-m-d H:i:s");

        foreach ($categories as $categorie) {
            if ($categorie->getName() == "") {
                continue;
            }

            if ($categorie->getId() == -1) {
                $id = $this->getNextCategoryId();
                $values = array(
                    'id' => array('integer', $id),
                    'name' => array('text', $categorie->getName()),
                    'changed_by' => array('integer', $changed_by),
                    'changed_at' => array('text', $today)
                );

                $this->getDB()->insert(self::TABLE_NAME_CATEGORIES, $values);
            } else {
                $values = array(
                    'name' => array('text', $categorie->getName()),
                    'changed_by' => array('integer', $changed_by),
                    'changed_at' => array('text', $today)
                );
                $where = array('id' => array('integer', $categorie->getId()));

                $this->getDB()->update(self::TABLE_NAME_CATEGORIES, $values, $where);
            }
        }
    }

    /**
     * @param int[] $ids
     */
    public function deleteCategories(array $ids)
    {
        $query =
             "DELETE FROM " . self::TABLE_NAME_CATEGORIES . PHP_EOL
            . "WHERE id in (" . implode(",", $ids) . ")" . PHP_EOL
        ;

        $this->getDB()->query($query);
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

    /**
     * Get next id for option entry
     */
    public function getNextCategoryId()
    {
        return (int) $this->getDB()->nextId(static::TABLE_NAME_CATEGORIES);
    }

    public function update1()
    {
        if ($this->getDB()->tableExists("xetr_astd_config")) {
            $this->getDB()->renameTable("xetr_astd_config", self::TABLE_NAME);
        }
        if ($this->getDB()->tableExists("xetr_astd_config_seq")) {
            $this->getDB()->renameTable("xetr_astd_config_seq", "xetr_gti_config_seq");
        }
        if ($this->getDB()->tableExists("xetr_astd_data")) {
            $this->getDB()->renameTable("xetr_astd_data", "xetr_gti_data");
        }
    }

    public function update2()
    {
        if (!$this->getDB()->tableExists(self::TABLE_NAME_CATEGORIES)) {
            $fields = array(
                'id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                'name' => array(
                        'type' => 'text',
                        'length' => 127,
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

            $this->getDB()->createTable(self::TABLE_NAME_CATEGORIES, $fields);
        }
    }

    public function update3()
    {
        $this->getDB()->addPrimaryKey(static::TABLE_NAME_CATEGORIES, array("id"));
    }

    public function update4()
    {
        $this->getDB()->createSequence(static::TABLE_NAME_CATEGORIES);
    }
}
