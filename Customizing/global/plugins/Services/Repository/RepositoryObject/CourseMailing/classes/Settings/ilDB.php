<?php
namespace CaT\Plugins\CourseMailing\Settings;

/**
 * Implemention for DB
 */
class ilDB implements DB
{
    const TABLE_NAME = "xcml_settings";
    const TABLE_NAME_HIST = "xcml_settings_hist";

    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var \ilObjUser
     */
    protected $user;

    public function __construct(\ilDBInterface $db, \ilObjUser $user)
    {
        $this->db = $db;
        $this->user = $user;
    }

    /**
     * @inheritdoc
     */
    public function create($obj_id, $days_invite, $days_remind_invitation, $prevent_mailing)
    {
        $setting = new Setting($obj_id, $days_invite, $days_remind_invitation, $prevent_mailing);
        $values = array(
            "obj_id" => array("integer", $setting->getObjectId()),
            "days_invitation" => array("integer", $setting->getDaysInvitation()),
            "days_invitation_reminder" => array("integer", $setting->getDaysRemindInvitation()),
            "prevent_mailing" => array("integer", $setting->getPreventMailing())
        );
        $this->db->insert(self::TABLE_NAME, $values);
        $this->createHistEntry($setting);

        return $setting;
    }

    /**
     * @inheritdoc
     */
    public function update(Setting $setting)
    {
        $where = array("obj_id" => array("integer", $setting->getObjectId()));
        $values = array(
            "days_invitation" => array("integer", $setting->getDaysInvitation()),
            "days_invitation_reminder" => array("integer", $setting->getDaysRemindInvitation()),
            "prevent_mailing" => array("integer", $setting->getPreventMailing())
        );
        $this->db->update(self::TABLE_NAME, $values, $where);
        $this->createHistEntry($setting);
    }

    /**
     * Creates a hist entry
     *
     * @param Setting 	$setting
     *
     * @return void
     */
    protected function createHistEntry(Setting $setting)
    {
        $values = array(
            "id" => array("integer", $this->nextId(self::TABLE_NAME_HIST)),
            "obj_id" => array("integer", $setting->getObjectId()),
            "days_invitation" => array("integer", $setting->getDaysInvitation()),
            "days_invitation_reminder" => array("integer", $setting->getDaysRemindInvitation()),
            "prevent_mailing" => array("integer", $setting->getPreventMailing()),
            "last_changed_by" => array("integer", $this->user->getId()),
            "last_changed" => array("text", date("Y-m-d H:i:s")),
        );

        $this->db->insert(self::TABLE_NAME_HIST, $values);
    }

    /**
     * @inheritdoc
     */
    public function selectForObject(int $obj_id)
    {
        $query = "SELECT obj_id, days_invitation, days_invitation_reminder, prevent_mailing" . PHP_EOL
                . " FROM " . self::TABLE_NAME . PHP_EOL
                . " WHERE obj_id = " . $this->db->quote($obj_id, "integer");

        $result = $this->db->query($query);
        if ($this->db->numRows($result) == 0) {
            return null;
        }
        $row = $this->db->fetchAssoc($result);

        $setting = new Setting(
            (int) $row["obj_id"],
            (int) $row["days_invitation"],
            (int) $row["days_invitation_reminder"],
            (bool) $row["prevent_mailing"]
        );

        return $setting;
    }

    /**
     * @inheritdoc
     */
    public function deleteForObject(int $obj_id)
    {
        $query = "DELETE FROM " . self::TABLE_NAME . PHP_EOL
            . " WHERE obj_id = " . $this->db->quote($obj_id, "integer");
        $this->db->manipulate($query);
    }

    /**
     * Get all log entries for obj id
     *
     * @param int 	$obj_id
     *
     * @return LogEntry[]
     */
    public function getLogEntriesFor(int $obj_id)
    {
        $query = "SELECT days_invitation, days_invitation_reminder," . PHP_EOL
                . " prevent_mailing, last_changed_by, last_changed" . PHP_EOL
                . " FROM " . self::TABLE_NAME_HIST . PHP_EOL
                . " WHERE obj_id = " . $this->db->quote($obj_id, "integer") . PHP_EOL
                . " ORDER BY id DESC";

        $ret = array();
        $res = $this->db->query($query);
        while ($row = $this->db->fetchAssoc($res)) {
            $change_date = \DateTime::createFromFormat("Y-m-d H:i:s", $row["last_changed"]);
            $ret[] = new LogEntry(
                (int) $row["last_changed_by"],
                $change_date,
                (int) $row["days_invitation"],
                (int) $row["days_invitation_reminder"],
                (bool) $row["prevent_mailing"]
            );
        }

        return $ret;
    }

    /**
     * Get next id for tabnle
     *
     * @param string 	$table
     *
     * @return int
     */
    protected function nextId($table)
    {
        return (int) $this->db->nextId($table);
    }


    /**
     * create table
     *
     * @return void
     */
    public function createTable()
    {
        if (!$this->db->tableExists(self::TABLE_NAME)) {
            $fields = array(
                'obj_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'days_invitation' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'days_invitation_reminder' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                )
            );
            $this->db->createTable(self::TABLE_NAME, $fields);
        }
    }

    /**
     * create table
     *
     * @return void
     */
    public function createHistTable()
    {
        if (!$this->db->tableExists(self::TABLE_NAME_HIST)) {
            $fields = array(
                'id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'obj_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'days_invitation' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'days_invitation_reminder' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'prevent_mailing' => array(
                    'type' => 'integer',
                    'length' => 1,
                    'notnull' => true
                ),
                'last_changed_by' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'last_changed' => array(
                    'type' => 'text',
                    'length' => 20,
                    'notnull' => true
                )
            );
            $this->db->createTable(self::TABLE_NAME_HIST, $fields);
        }
    }

    /**
     * Configure primary key on table
     *
     * @return void
     */
    public function createHistPrimaryKey()
    {
        $this->db->addPrimaryKey(self::TABLE_NAME_HIST, array("id"));
    }

    /**
     * Create sequence for hist
     *
     * @return void
     */
    public function createHistSequence()
    {
        $this->db->createSequence(self::TABLE_NAME_HIST);
    }

    /**
     * Configure primary key on table
     *
     * @return void
     */
    public function createPrimaryKey()
    {
        $this->db->addPrimaryKey(self::TABLE_NAME, array("obj_id"));
    }

    /**
     * Update 1
     */
    public function update1()
    {
        if ($this->db->tableExists(self::TABLE_NAME)) {
            if (!$this->db->tableColumnExists(self::TABLE_NAME, 'prevent_mailing')) {
                $this->db->addTableColumn(self::TABLE_NAME, 'prevent_mailing', array(
                    'type' => 'integer',
                    'length' => 1,
                    'notnull' => true
                ));
            }
        }
    }
}
