<?php

namespace CaT\Plugins\MaterialList\Settings;

/**
 * DB interface implementation for settings
 */
class ilDB implements DB
{
    const TABLE_NAME = "xmat_settings";

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
    public function create($obj_id, \ilDateTime $last_edit_datetime, $last_edit_by, $recipient_mode, $send_days_before = null)
    {
        assert('is_int($obj_id)');
        assert('is_int($last_edit_by)');
        assert('is_string($recipient_mode)');
        assert('is_int($send_days_before) || is_null($send_days_before)');

        $material_list = new MaterialList($obj_id, $last_edit_datetime, $last_edit_by, $recipient_mode, $send_days_before);

        $values = array( "obj_id" => array("integer", $material_list->getObjId())
                , "last_edit_datetime" => array("text", $material_list->getLastEditDateTime())
                , "last_edit_by" => array("integer", $material_list->getLastEditBy())
                , "recipient_mode" => array("text", $material_list->getRecipientMode())
                , "send_days_before" => array("text", $material_list->getSendDaysBefore())
                );

        $this->getDB()->insert(self::TABLE_NAME, $values);
    }

    /**
     * @inheritdoc
     */
    public function update(MaterialList $material_list)
    {
        $where = array("obj_id" => array("integer", $material_list->getObjId()));

        $values = array( "last_edit_datetime" => array("text", $material_list->getLastEditDateTime())
                , "last_edit_by" => array("integer", $material_list->getLastEditBy())
                , "recipient_mode" => array("text", $material_list->getRecipientMode())
                , "recipient" => array("text", $material_list->getRecipient())
                , "send_days_before" => array("integer", $material_list->getSendDaysBefore())
                );

        $this->getDB()->update(self::TABLE_NAME, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function selectFor($obj_id)
    {
        assert('is_int($obj_id)');

        $query = "SELECT last_edit_datetime, last_edit_by, recipient_mode, recipient, send_days_before\n"
                . " FROM " . self::TABLE_NAME . "\n"
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");

        $res = $this->getDB()->query($query);

        if ($this->getDB()->numRows($res) == 0) {
            throw new \LogicException(__METHOD__ . ":: no settings for object with id " . $obj_id . " found");
        }

        $row = $this->getDB()->fetchAssoc($res);
        $last_edit_datetime = new \ilDateTime($row["last_edit_datetime"], IL_CAL_DATETIME);
        $send_days_before = $row["send_days_before"];
        if ($send_days_before !== null) {
            $send_days_before = (int) $send_days_before;
        }
        $material_list = new MaterialList($obj_id, $last_edit_datetime, (int) $row["last_edit_by"], $row["recipient_mode"], $row["recipient"], $send_days_before);

        return $material_list;
    }

    /**
     * @inheritdoc
     */
    public function deleteFor($obj_id)
    {
        assert('is_int($obj_id)');

        $query = "DELETE FROM " . self::TABLE_NAME . "\n"
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");

        $this->getDB()->manipulate($query);
    }

    /**
     * Creates needed tables
     *
     * @return null
     */
    protected function createTable()
    {
        if (!$this->getDB()->tableExists(self::TABLE_NAME)) {
            $fields =
                array('obj_id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'last_edit_datetime' => array(
                        'type' => 'timestamp',
                        'notnull' => true
                    ),
                    'last_edit_by' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    )
                );

            $this->getDB()->createTable(self::TABLE_NAME, $fields);
            $this->getDB()->addPrimaryKey(self::TABLE_NAME, array("obj_id"));
        }
    }

    /**
     * Update 1
     *
     * @return void
     */
    public function update1()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "recipient_mode")) {
            $field = array(
                        'type' => 'text',
                        'length' => 50,
                        'notnull' => false
                    );

            $this->getDB()->addTableColumn(self::TABLE_NAME, "recipient_mode", $field);
        }

        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "recipient")) {
            $field = array(
                        'type' => 'text',
                        'length' => 256,
                        'notnull' => false
                    );

            $this->getDB()->addTableColumn(self::TABLE_NAME, "recipient", $field);
        }

        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "send_days_before")) {
            $field = array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => false
                    );

            $this->getDB()->addTableColumn(self::TABLE_NAME, "send_days_before", $field);
        }
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
