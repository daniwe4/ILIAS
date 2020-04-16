<?php

declare(strict_types=1);

namespace CaT\Plugins\CourseMember\LPOptions;

/**
 * Implementation DB interface for ILIAS
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilDB implements DB
{
    const TABLE_NAME = "xcmb_lp_options";

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
    public function create(string $title, int $ilias_lp, bool $active, bool $standard) : LPOption
    {
        $next_id = $this->getNextId();
        $lp_option = new LPOption($next_id, $title, $ilias_lp, $active, false);

        $values = array("id" => array("integer", $lp_option->getId()),
            "title" => array("text", $lp_option->getTitle()),
            "ilias_lp" => array("integer", $lp_option->getILIASLP()),
            "active" => array("integer", $lp_option->getActive()),
            "standard" => array("integer", $lp_option->isStandard())
        );

        $this->getDB()->insert(self::TABLE_NAME, $values);

        return $lp_option;
    }

    /**
     * @inheritdoc
     */
    public function update(LPOption $lp_option)
    {
        $where = array("id" => array("integer", $lp_option->getId()));

        $values = array("title" => array("text", $lp_option->getTitle()),
            "ilias_lp" => array("integer", $lp_option->getILIASLP()),
            "active" => array("integer", $lp_option->getActive()),
            "standard" => array("integer", $lp_option->isStandard())
        );

        $this->getDB()->update(self::TABLE_NAME, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function select(bool $only_active = false) : array
    {
        $query = "SELECT id, title, ilias_lp, active, standard" . PHP_EOL
                . " FROM " . self::TABLE_NAME;

        if ($only_active) {
            $query .= PHP_EOL . " WHERE active = 1";
        }

        $ret = array();
        $res = $this->getDB()->query($query);
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $ilias_lp = $row["ilias_lp"];
            if ($ilias_lp !== null) {
                $ilias_lp = (int) $ilias_lp;
            }

            $ret[] = $this->getLPOptionWith(
                (int) $row["id"],
                $row["title"],
                $ilias_lp,
                (bool) $row["active"],
                (bool) $row["standard"]
            );
        }

        return $ret;
    }

    /**
     * @inheritdoc
     */
    public function deleteAll()
    {
        $query = "DELETE FROM " . self::TABLE_NAME;
        $this->getDB()->manipulate($query);
    }

    /**
     * @inheritdoc
     */
    public function delete(int $id)
    {
        $query = "DELETE FROM " . self::TABLE_NAME . PHP_EOL
                . " WHERE id = " . $this->getDB()->quote($id, "integer");
        $this->getDB()->manipulate($query);
    }

    /**
     * Get an empty lp option
     *
     * @return LPOption
     */
    public function getEmptyLPOption(int $id)
    {
        return new LPOption($id, "", -1, false, false);
    }

    /**
     * Get an filled lp option
     *
     * @param int 	$id
     * @param string 	$title
     * @param int | null	$ilias_lp
     * @param bool 	$active
     *
     * @return LPOption
     */
    public function getLPOptionWith(int $id, string $title, int $ilias_lp, bool $active, bool $default)
    {
        return new LPOption($id, $title, $ilias_lp, $active, $default);
    }

    /**
     * Get the title of lp option by id
     *
     * @param int 	$id
     *
     * @return string | null
     */
    public function getLPOptionTitleBy(int $id)
    {
        $query = "SELECT title" . PHP_EOL
                . " FROM " . self::TABLE_NAME . PHP_EOL
                . " WHERE id = " . $this->getDB()->quote($id, "integer");

        $res = $this->getDB()->query($query);
        if ($this->getDB()->numRows($res) == 0) {
            return null;
        }

        $row = $this->getDB()->fetchAssoc($res);
        return $row["title"];
    }

    /**
     * Get the ilias lp by id
     */
    public function getILIASLPBy(int $id) : int
    {
        $query = "SELECT ilias_lp" . PHP_EOL
                . " FROM " . self::TABLE_NAME . PHP_EOL
                . " WHERE id = " . $this->getDB()->quote($id, "integer");

        $res = $this->getDB()->query($query);
        $row = $this->getDB()->fetchAssoc($res);
        return (int) $row["ilias_lp"];
    }

    /**
     * Create the table
     *
     * @return void
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
                    'title' => array(
                        'type' => 'text',
                        'length' => 128,
                        'notnull' => true
                    ),
                    'ilias_lp' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => false
                    ),
                    'active' => array(
                        'type' => 'integer',
                        'length' => 1,
                        'notnull' => true
                    )
                );

            $this->getDB()->createTable(static::TABLE_NAME, $fields);
        }
    }

    /**
     * Create primary key
     *
     * @return void
     */
    public function createPrimaryKey()
    {
        if (!$this->getDB()->indexExistsByFields(self::TABLE_NAME, array("id"))) {
            $this->getDB()->addPrimaryKey(self::TABLE_NAME, array("id"));
        }
    }

    /**
     * Create sequence for primary key ids
     *
     * @return void
     */
    public function createSequence()
    {
        if (!$this->getDB()->sequenceExists(self::TABLE_NAME)) {
            $this->getDB()->createSequence(self::TABLE_NAME);
        }
    }

    /**
     * Drop table
     *
     * @return void
     */
    public function dropTable()
    {
        if ($this->getDB()->tableExists(self::TABLE_NAME)) {
            $this->getDB()->dropTable(self::TABLE_NAME);
        }
    }

    /**
     * Drop sequence
     *
     * @return void
     */
    public function dropSequence()
    {
        if ($this->getDB()->sequenceExists(self::TABLE_NAME)) {
            $this->getDB()->dropSequence(self::TABLE_NAME);
        }
    }

    /**
     * Drop sequence
     *
     * @return void
     */
    public function update1()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "standard")) {
            $field = array(
                'type' => 'integer',
                'length' => 4,
                'default' => 0
            );
            $this->getDB()->addTableColumn(self::TABLE_NAME, "standard", $field);
        }
    }

    /**
     * Get intance of db
     *
     * @throws \Exception
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
     *
     * @return int
     */
    public function getNextId()
    {
        return (int) $this->getDB()->nextId(static::TABLE_NAME);
    }
}
