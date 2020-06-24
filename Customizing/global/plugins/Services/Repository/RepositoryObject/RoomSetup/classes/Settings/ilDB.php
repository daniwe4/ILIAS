<?php

namespace CaT\Plugins\RoomSetup\Settings;

/**
 * DB interface implementation for settings
 */
class ilDB implements DB
{
    const TABLE_NAME = "xrse_settings";

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function create(int $obj_id, $recipient_mode = null, $recipient = null, $send_days_before = null)
    {
        $ret = array();
        foreach (RoomSetup::getPossibleSettingTypes() as $setting_type) {
            $room_setup = new RoomSetup($obj_id, $setting_type, $recipient_mode, $recipient, $send_days_before);
            $values = array(
                "obj_id" => array("integer", $room_setup->getObjId()),
                "recipient_mode" => array("text", $room_setup->getRecipientMode()),
                "setting_type" => array("integer", $room_setup->getType())
            );
            $this->getDB()->insert(self::TABLE_NAME, $values);
            $ret[] = $room_setup;
        }

        return $ret;
    }

    /**
     * @inheritdoc
     */
    public function update(RoomSetup $room_setup)
    {
        $where = array(
            "obj_id" => array("integer", $room_setup->getObjId()),
            "setting_type" => array("integer", $room_setup->getType())
        );

        $values = array( "recipient_mode" => array("text", $room_setup->getRecipientMode())
                , "recipient" => array("text", $room_setup->getRecipient())
                , "send_days_before" => array("integer", $room_setup->getSendDaysBefore())
                );

        $this->getDB()->update(self::TABLE_NAME, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function selectFor(int $obj_id)
    {
        $query = "SELECT setting_type, recipient_mode, recipient, send_days_before\n"
                . " FROM " . self::TABLE_NAME . "\n"
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");

        $res = $this->getDB()->query($query);

        if ($this->getDB()->numRows($res) == 0) {
            throw new \Exception("No settings for obj_id " . $obj_id, 1);
        }

        $settings = [];
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $send_days_before = $row["send_days_before"];
            if ($send_days_before !== null) {
                $send_days_before = (int) $send_days_before;
            }

            $setting = new RoomSetup(
                $obj_id,
                (int) $row["setting_type"],
                $row["recipient_mode"],
                $row["recipient"],
                $send_days_before
            );

            $settings[] = $setting;
        }

        return $settings;
    }

    /**
     * @inheritdoc
     */
    public function deleteFor(int $obj_id)
    {
        $query = "DELETE FROM " . self::TABLE_NAME . "\n"
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");

        $this->getDB()->manipulate($query);
    }

    /**
     * Creates needed tables
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
                    'recipient_mode' => array(
                        'type' => 'text',
                        'length' => 50,
                        'notnull' => false
                    ),
                    'recipient' => array(
                        'type' => 'text',
                        'length' => 256,
                        'notnull' => false
                    ),
                    'send_days_before' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => false
                    )
                );

            $this->getDB()->createTable(self::TABLE_NAME, $fields);
        }
    }

    /**
     * Create primary key
     *
     * @return void
     */
    public function createPrimary()
    {
        $this->getDB()->addPrimaryKey(self::TABLE_NAME, array("obj_id"));
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

    /**
     * Extend table with identifier-field
     *
     * @return void
     */
    public function update1()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "setting_type")) {
            $field = array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true
            );
            $this->getDB()->addTableColumn(self::TABLE_NAME, "setting_type", $field);
        }
    }

    /**
     * extend primary
     *
     * @return void
     */
    public function update2()
    {
        $this->getDB()->dropPrimaryKey(self::TABLE_NAME);
        $this->getDB()->addPrimaryKey(self::TABLE_NAME, array('obj_id', 'setting_type'));
    }

    /**
     * Update existing settings, set default
     *
     * @return void
     */
    public function update3()
    {
        $where = array('setting_type' => array("integer", 0));
        $values = array(
            "setting_type" => array("integer", RoomSetup::TYPE_SERVICE)
        );
        $this->getDB()->update(self::TABLE_NAME, $values, $where);
    }

    /**
     * Update existing settings, insert TYPE_ROOMSETUP
     *
     * @return void
     */
    public function update4()
    {
        $query = "SELECT * FROM " . self::TABLE_NAME;
        $res = $this->getDB()->query($query);
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $values = array(
                "obj_id" => array("int", $row['obj_id']),
                "recipient_mode" => array("text", $row['recipient_mode']),
                "recipient" => array("text", $row['recipient']),
                "send_days_before" => array("integer", $row['send_days_before']),
                "setting_type" => array("integer", RoomSetup::TYPE_ROOMSETUP)
            );
            $this->getDB()->insert(self::TABLE_NAME, $values);
        }
    }
}
