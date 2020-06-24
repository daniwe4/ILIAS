<?php

namespace CaT\Plugins\CourseMember\Settings;

/**
 * Interface for DB handle of additional setting values
 */
class ilDB implements DB
{
    const TABLE_NAME = "xcmb_settings";

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
    public function update(CourseMemberSettings $settings)
    {
        $where = array("obj_id" => array("integer", $settings->getObjId()));
        $values = array("credits" => array("float", $settings->getCredits()),
            "closed" => array("integer", $settings->getClosed()),
            "lp_mode" => array("integer", $settings->getLPMode()),
            "list_required" => array("integer", $settings->getListRequired()),
            "list_with_orgu" => array("integer", $settings->getListOptionOrgu()),
            "list_with_text" => array("integer", $settings->getListOptionText())
        );

        $this->getDB()->update(self::TABLE_NAME, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function create(
        int $obj_id,
        $credits,
        bool $closed = false,
        int $lp_mode = 0,
        bool $list_required = false,
        bool $opt_orgu = false,
        bool $opt_text = true
    ) : CourseMemberSettings {
        $settings = new CourseMemberSettings(
            $obj_id,
            $credits,
            $closed,
            $lp_mode,
            $list_required,
            $opt_orgu,
            $opt_text
        );

        $values = array("obj_id" => array("integer", $settings->getObjId()),
            "credits" => array("float", $settings->getCredits()),
            "closed" => array("integer", $settings->getClosed()),
            "lp_mode" => array("integer", $settings->getLPMode()),
            "list_required" => array("integer", $settings->getListRequired()),
            "list_with_orgu" => array("integer", $settings->getListOptionOrgu()),
            "list_with_text" => array("integer", $settings->getListOptionText())
        );

        $this->getDB()->insert(self::TABLE_NAME, $values);

        return $settings;
    }

    /**
     * @inheritdoc
     */
    public function selectFor(int $obj_id) : CourseMemberSettings
    {
        $query = "SELECT obj_id, credits, closed, lp_mode, list_required, list_with_orgu, list_with_text" . PHP_EOL
                . " FROM " . self::TABLE_NAME . PHP_EOL
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");

        $res = $this->getDB()->query($query);
        $row = $this->getDB()->fetchAssoc($res);

        $credits = $row["credits"];
        if ($credits !== null) {
            $credits = (float) $credits;
        }

        return new CourseMemberSettings(
            (int) $row["obj_id"],
            $credits,
            (bool) $row["closed"],
            (int) $row["lp_mode"],
            (bool) $row["list_required"],
            (bool) $row["list_with_orgu"],
            (bool) $row["list_with_text"]
        );
    }

    /**
     * @inheritdoc
     */
    public function deleteFor(int $obj_id)
    {
        $query = "DELETE FROM " . self::TABLE_NAME . PHP_EOL
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");

        $this->getDB()->manipulate($query);
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
                array('obj_id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'credits' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => false
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
        if (!$this->getDB()->indexExistsByFields(self::TABLE_NAME, array("obj_id"))) {
            $this->getDB()->addPrimaryKey(self::TABLE_NAME, array("obj_id"));
        }
    }

    /**
     * Updates table columns
     *
     * @return void
     */
    public function update1()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "closed")) {
            $field = array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => false
            );

            $this->getDB()->addTableColumn(self::TABLE_NAME, "closed", $field);
        }
    }

    /**
     * Updates table columns
     *
     * @return void
     */
    public function update2()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "lp_mode")) {
            $field = array(
                'type' => 'integer',
                'length' => 4,
                'default' => 0,
                'notnull' => false
            );

            $this->getDB()->addTableColumn(self::TABLE_NAME, "lp_mode", $field);
        }
    }

    /**
     * Updates table columns
     *
     * @return void
     */
    public function update3()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "list_required")) {
            $field = array(
                'type' => 'integer',
                'length' => 1,
                'default' => 0,
                'notnull' => false
            );

            $this->getDB()->addTableColumn(self::TABLE_NAME, "list_required", $field);
        }
    }

    /**
     * Updates table columns
     *
     * @return void
     */
    public function update4()
    {
        if ($this->getDB()->tableColumnExists(self::TABLE_NAME, "credits")) {
            $field = array(
                'type' => 'float',
                'notnull' => false
            );

            $this->getDB()->modifyTableColumn(self::TABLE_NAME, "credits", $field);
        }
    }

    /**
     * Updates table columns
     *
     * @return void
     */
    public function update5()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "list_with_orgu")) {
            $field = array(
                'type' => 'integer',
                'length' => 1,
                'default' => 0,
                'notnull' => false
            );
            $this->getDB()->addTableColumn(self::TABLE_NAME, "list_with_orgu", $field);
        }

        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "list_with_text")) {
            $field = array(
                'type' => 'integer',
                'length' => 1,
                'default' => 1,
                'notnull' => false
            );
            $this->getDB()->addTableColumn(self::TABLE_NAME, "list_with_text", $field);
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
}
