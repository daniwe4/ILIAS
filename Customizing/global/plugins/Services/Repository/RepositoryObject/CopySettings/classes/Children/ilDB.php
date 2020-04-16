<?php

namespace CaT\Plugins\CopySettings\Children;

/**
 * Implementaiton of handling with copy settings
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-traning.de>
 */
class ilDB implements DB
{
    const TABLE_NAME = "xcps_copy_settings";

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
    public function create($obj_id, $target_ref_id, $target_obj_id, $is_referenced, $process_type)
    {
        assert('is_int($obj_id)');
        assert('is_int($target_ref_id)');
        assert('is_int($target_obj_id)');
        assert('is_bool($is_referenced)');
        assert('is_string($process_type)');

        $copy_settings = new Child($obj_id, $target_ref_id, $target_obj_id, $is_referenced, $process_type);

        $values = array("obj_id" => array("integer", $copy_settings->getObjId()),
            "target_ref_id" => array("integer", $copy_settings->getTargetRefId()),
            "target_obj_id" => array("integer", $copy_settings->getTargetObjId()),
            "is_referenced" => array("integer", $copy_settings->isReferenced()),
            "process_type" => array("text", $copy_settings->getProcessType())
        );

        $this->getDB()->insert(self::TABLE_NAME, $values);

        return $copy_settings;
    }

    /**
     * @inheritdoc
     */
    public function select($obj_id)
    {
        assert('is_int($obj_id)');

        $query = "SELECT obj_id, target_ref_id, target_obj_id, is_referenced, process_type\n"
                . " FROM " . self::TABLE_NAME . "\n"
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");

        $res = $this->getDB()->query($query);

        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $ret[] = new Child(
                (int) $row["obj_id"],
                (int) $row["target_ref_id"],
                (int) $row["target_obj_id"],
                (bool) $row["is_referenced"],
                $row["process_type"]
            );
        }

        return $ret;
    }

    /**
     * @inheritdoc
     */
    public function delete($obj_id)
    {
        assert('is_int($obj_id)');

        $query = "DELETE FROM " . self::TABLE_NAME . "\n"
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");

        $this->getDB()->manipulate($query);
    }

    /**
     * Create table
     *
     * @return void
     */
    public function createTable()
    {
        if (!$this->getDB()->tableExists(static::TABLE_NAME)) {
            $fields =
                array('obj_id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'target_ref_id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'target_obj_id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'is_referenced' => array(
                        'type' => 'integer',
                        'length' => 1,
                        'notnull' => true
                    ),
                    'process_type' => array(
                        'type' => 'text',
                        'length' => 32,
                        'notnull' => true
                    )
                );

            $this->getDB()->createTable(static::TABLE_NAME, $fields);
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
            $this->getDB()->addPrimaryKey(self::TABLE_NAME, array("obj_id", "target_obj_id"));
        }
    }

    /**
     * Update database
     *
     * @return void
     */
    public function update1()
    {
        if ($this->getDB()->tableExists(self::TABLE_NAME)) {
            $this->getDB()->dropPrimaryKey(self::TABLE_NAME);
            $this->getDB()->addPrimaryKey(self::TABLE_NAME, array("obj_id", "target_ref_id", "target_obj_id"));
        }
    }

    /**
     * Delete child entries by obj id
     *
     * @param int 	$target_obj_id
     *
     * @return void
     */
    public function deleteCopySettingsByTargetObjId($target_obj_id)
    {
        assert('is_int($target_obj_id)');
        $query = "DELETE FROM " . self::TABLE_NAME . "\n"
                . " WHERE target_obj_id = " . $this->getDB()->quote("integer", $target_obj_id);

        $this->getDB()->manipulate($query);
    }

    /**
     * Delete child entries by ref id
     *
     * @param int 	$target_ref_id
     *
     * @return void
     */
    public function deleteCopySettingsTargetByRefId($target_ref_id)
    {
        assert('is_int($target_ref_id)');
        $query = "DELETE FROM " . self::TABLE_NAME . "\n"
                . " WHERE target_ref_id = " . $this->getDB()->quote("integer", $target_ref_id);

        $this->getDB()->manipulate($query);
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
