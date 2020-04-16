<?php

namespace CaT\Plugins\CopySettings\TemplateCourses;

class ilDB implements DB
{
    const TABLE_NAME = "xcps_tpl_crs";

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
    public function create($obj_id, $crs_id, $crs_ref_id)
    {
        assert('is_int($obj_id)');
        assert('is_int($crs_id)');
        assert('is_int($crs_ref_id)');
        $values = array(
            "obj_id" => array("integer", $obj_id),
            "crs_id" => array("integer", $crs_id),
            "crs_ref_id" => array("integer", $crs_ref_id)
        );

        $this->getDB()->insert(self::TABLE_NAME, $values);
    }

    /**
     * @inheritdoc
     */
    public function deleteFor($obj_id)
    {
        assert('is_int($obj_id)');
        $query = "DELETE FROM " . self::TABLE_NAME . PHP_EOL
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");
        $this->getDB()->manipulate($query);
    }

    /**
     * @inheritdoc
     */
    public function isTemplateByObjId($crs_id)
    {
        $query = "SELECT COUNT(obj_id) AS cnt" . PHP_EOL
                . " FROM " . self::TABLE_NAME . PHP_EOL
                . " WHERE crs_id = " . $this->getDB()->quote("crs_id", $crs_id, "integer");

        $res = $this->getDB()->query($query);
        $row = $this->fetchAssoc($res);

        return count($row["cnt"]) > 0;
    }

    /**
     * @inheritdoc
     */
    public function isTemplateByRefId($crs_ref_id)
    {
        $query = "SELECT COUNT(obj_id) AS cnt" . PHP_EOL
                . " FROM " . self::TABLE_NAME . PHP_EOL
                . " WHERE crs_ref_id = " . $this->getDB()->quote("crs_ref_id", $crs_ref_id, "integer");

        $res = $this->getDB()->query($query);
        $row = $this->fetchAssoc($res);

        return count($row["cnt"]) > 0;
    }

    /**
     * Create table
     *
     * @return void
     */
    public function createTable()
    {
        if (!$this->getDB()->tableExists(self::TABLE_NAME)) {
            $fields =
                array(
                    'obj_id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'crs_id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'crs_ref_id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    )
                );

            $this->getDB()->createTable(self::TABLE_NAME, $fields);
        }
    }

    /**
     * Sets primary key
     *
     * @return void
     */
    public function createPrimaryKey()
    {
        if ($this->getDB()->tableExists(self::TABLE_NAME)) {
            $this->getDB()->addPrimaryKey(self::TABLE_NAME, array("obj_id", "crs_id", "crs_ref_id"));
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
