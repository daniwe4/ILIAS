<?php

declare(strict_types=1);

namespace CaT\Plugins\Webinar\VC\CSN;

use CaT\Plugins\Webinar\VC;

/**
 * Implementation to save settings and imported user for CSN VC
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilDB implements DB
{
    const TABLE_SETTNGS = "xwbr_csn_settings";
    const TABLE_UNKNOWN_PARTICIPANTS = "xwbr_csn_unknown";
    const TABLE_PARTICIPANTS = "xwbr_participants";
    const TABLE_USER_DATA = "usr_data";

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
    public function create(
        int $obj_id,
        ?string $phone,
        ?string $pin,
        ?int $minutes_required,
        bool $upload_required = false
    ) : Settings {
        $settings = new Settings($obj_id, $phone, $pin, $minutes_required);

        $values = array("obj_id" => array("integer", $settings->getObjId()),
            "phone" => array("text", $settings->getPhone()),
            "pin" => array("text", $settings->getPin()),
            "minutes_required" => array("integer", $settings->getMinutesRequired()),
            "upload_required" => array("integer", $settings->isUploadRequired())
        );

        $this->getDB()->insert(self::TABLE_SETTNGS, $values);

        return $settings;
    }

    /**
     * @inheritdoc
     */
    public function update(Settings $settings) : void
    {
        $where = array("obj_id" => array("integer", $settings->getObjId()));

        $values = array("phone" => array("text", $settings->getPhone()),
            "pin" => array("text", $settings->getPin()),
            "minutes_required" => array("integer", $settings->getMinutesRequired()),
            "upload_required" => array("integer", $settings->isUploadRequired())
        );

        $this->getDB()->update(self::TABLE_SETTNGS, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function select(int $obj_id) : Settings
    {
        $query = "SELECT obj_id, phone, pin, minutes_required, upload_required\n"
                . " FROM " . self::TABLE_SETTNGS . "\n"
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");

        $res = $this->getDB()->query($query);

        if ($this->getDB()->numRows($res) == 0) {
            throw new \Exception(__METHOD__ . " no settings found for obj_id: " . $obj_id);
        }

        $row = $this->getDB()->fetchAssoc($res);

        return new Settings(
            (int) $row["obj_id"],
            $row["phone"],
            $row["pin"],
            (int) $row["minutes_required"],
            (bool) $row["upload_required"]
        );
    }

    /**
     * @inheritdoc
     */
    public function delete(int $obj_id) : void
    {
        $this->deleteUnknownParticipants($obj_id);

        $query = "DELETE FROM " . self::TABLE_SETTNGS . "\n"
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");

        $this->getDB()->manipulate($query);
    }

    /**
     * @inheritdoc
     */
    public function createUnknownParticipant(
        int $obj_id,
        string $user_name,
        string $email,
        string $phone,
        string $company,
        int $minutes
    ) : VC\Participant {
        $id = $this->getNextId(self::TABLE_UNKNOWN_PARTICIPANTS);
        $participant = new UnknownParticipant($id, $obj_id, "", $email, $phone, $company, $minutes, $user_name, null);

        $values = array("id" => array("integer", $participant->getId()),
            "obj_id" => array("integer", $participant->getObjId()),
            "user_name" => array("string", $participant->getUserName()),
            "email" => array("string", $participant->getEmail()),
            "phone" => array("string", $participant->getPhone()),
            "company" => array("string", $participant->getCompany()),
            "minutes" => array("integer", $participant->getMinutes())
        );

        $this->getDB()->insert(self::TABLE_UNKNOWN_PARTICIPANTS, $values);

        return $participant;
    }

    /**
     * @inheritdoc
     */
    public function deleteUnknownParticipants(int $obj_id) : void
    {
        $query = "DELETE FROM " . self::TABLE_UNKNOWN_PARTICIPANTS . "\n"
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");

        $this->getDB()->manipulate($query);
    }

    /**
     * Delete a single unlnown participant
     *
     * @param int 	$id
     *
     * @return void
     */
    public function deleteUnknownParticipant(int $id) : void
    {
        $query = "DELETE FROM " . self::TABLE_UNKNOWN_PARTICIPANTS . PHP_EOL
                . " WHERE id = " . $this->getDB()->quote($id, "integer");

        $this->getDB()->manipulate($query);
    }

    /**
     * Reset all minute of booked users after delete imported file
     *
     * @param int 	$obj_id
     *
     * @return void
     */
    public function resetMinutesOfBookedUsers(int $obj_id) : void
    {
        $where = array("obj_id" => array("integer", $obj_id));

        $values = array("minutes" => array("integer", null));

        $this->getDB()->update(self::TABLE_PARTICIPANTS, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function getUnknownParticipants(int $obj_id) : array
    {
        $query = "SELECT A.id, A.obj_id, A.user_name, A.email, A.phone, A.company, A.minutes,\n"
                . " B.usr_id, B.firstname, B.lastname\n"
                . " FROM " . self::TABLE_UNKNOWN_PARTICIPANTS . " A\n"
                . " LEFT JOIN usr_data B\n"
                . "     ON A.user_name = B.login\n"
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");

        $res = $this->getDB()->query($query);

        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $user_id = $row["usr_id"];
            if ($user_id !== null) {
                $user_id = (int) $user_id;
            }
            $ret[] = new UnknownParticipant(
                (int) $row["id"],
                (int) $row["obj_id"],
                $row["firstname"] . " " . $row["lastname"],
                $row["email"],
                $row["phone"],
                $row["company"],
                (int) $row["minutes"],
                $row["user_name"],
                $user_id
            );
        }

        return $ret;
    }

    /**
     * Get a single unknown participant by user name
     *
     * @param int 	$obj_id
     * @param string 	$user_name
     *
     * @return UnknownParticipant | null
     */
    public function getUnknownParticipantByLogin(int $obj_id, string $user_name) : ?VC\Participant
    {
        $query = "SELECT A.id, A.obj_id, A.user_name, A.email, A.phone, A.company, A.minutes," . PHP_EOL
                . " B.usr_id, B.firstname, B.lastname" . PHP_EOL
                . " FROM " . self::TABLE_UNKNOWN_PARTICIPANTS . " A" . PHP_EOL
                . " LEFT JOIN usr_data B" . PHP_EOL
                . "     ON A.user_name = B.login" . PHP_EOL
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer") . PHP_EOL
                . "     AND user_name = " . $this->getDB()->quote($user_name, "text");

        $res = $this->getDB()->query($query);
        if ($this->getDB()->numRows($res) == 0) {
            return null;
        }

        $row = $this->getDB()->fetchAssoc($res);

        return new UnknownParticipant(
            (int) $row["id"],
            (int) $row["obj_id"],
            $row["firstname"] . " " . $row["lastname"],
            $row["email"],
            $row["phone"],
            $row["company"],
            (int) $row["minutes"],
            $row["user_name"],
            $user_id
            );
    }

    /**
     * Get booked participants as CSN Participant object
     *
     * @param int 		$obj_id
     * @param string 	$phone_type
     *
     * @return Participant[]
     */
    public function getBookedParticipants(int $obj_id, string $phone_type) : array
    {
        $query = "SELECT A.obj_id, A.user_id, A.user_name, A.minutes,\n"
                . " B.firstname, B.lastname, B.institution, B." . $phone_type . ", B.email"
                . " FROM " . self::TABLE_PARTICIPANTS . " A\n"
                . " JOIN " . self::TABLE_USER_DATA . " B\n"
                . "     ON A.user_id = B.usr_id\n"
                . " WHERE A.obj_id = " . $this->getDB()->quote($obj_id, "integer");

        $res = $this->getDB()->query($query);

        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $ret[] = new Participant(
                (int) $row["user_id"],
                (int) $row["obj_id"],
                $this->getDefaultValue($row["firstname"], "") . " " . $this->getDefaultValue($row["lastname"], ""),
                $this->getDefaultValue($row["email"], ""),
                $this->getDefaultValue($row[$phone_type], ""),
                $this->getDefaultValue($row["institution"], ""),
                (int) $this->getDefaultValue($row["minutes"], 0),
                $this->getDefaultValue($row["user_name"], ""),
                (int) $row["user_id"]
            );
        }

        return $ret;
    }

    /**
     * Get booked user by user name
     *
     * @param int 		$obj_id
     * @param string 	$user_name
     * @param string 	$phone_type
     *
     * @return Participant | null
     */
    public function getParticipantByUserName(int $obj_id, string $user_name, string $phone_type) : ?VC\Participant
    {
        $query = "SELECT A.obj_id, A.user_id, A.user_name, A.minutes,\n"
                . " B.firstname, B.lastname, B.institution, B." . $phone_type . ", B.email"
                . " FROM " . self::TABLE_PARTICIPANTS . " A\n"
                . " JOIN " . self::TABLE_USER_DATA . " B\n"
                . "     ON A.user_id = B.usr_id\n"
                . " WHERE A.obj_id = " . $this->getDB()->quote($obj_id, "integer") . "\n"
                . "     AND A.user_name = " . $this->getDB()->quote($user_name, "text");

        $res = $this->getDB()->query($query);
        if ($this->getDB()->numRows($res) == 0) {
            return null;
        }

        $row = $this->getDB()->fetchAssoc($res);
        return new Participant(
            (int) $row["user_id"],
            (int) $row["obj_id"],
            $this->getDefaultValue($row["firstname"], "") . " " . $this->getDefaultValue($row["lastname"], ""),
            $this->getDefaultValue($row["email"], ""),
            $this->getDefaultValue($row[$phone_type], ""),
            $this->getDefaultValue($row["institution"], ""),
            (int) $this->getDefaultValue($row["minutes"], 0),
            $this->getDefaultValue($row["user_name"], ""),
            (int) $row["user_id"]
        );
    }

    /**
     * Update a booked participant with vc file data
     *
     * @param Participant 	$participant
     *
     * @return void
     */
    public function updateParticipant(VC\Participant $participant) : void
    {
        $where = array("obj_id" => array("integer", $participant->getObjId()),
            "user_id" => array("integer", $participant->getUserId()),
            "user_name" => array("integer", $participant->getUserName())
        );

        $values = array("minutes" => array("integer", $participant->getMinutes()));

        $this->getDB()->update(self::TABLE_PARTICIPANTS, $values, $where);
    }

    /**
     * Get default value if $value is null
     *
     * @return mixed
     */
    protected function getDefaultValue($value, $default)
    {
        if ($value !== null) {
            return $value;
        }

        return $default;
    }

    /**
     * Create tables for CSN VC
     *
     * @return void
     */
    public function createTable() : void
    {
        if (!$this->getDB()->tableExists(self::TABLE_SETTNGS)) {
            $fields = array(
                "obj_id" => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                "phone" => array(
                    'type' => 'text',
                    'length' => 64,
                    'notnull' => false
                ),
                "pin" => array(
                    'type' => 'text',
                    'length' => 32,
                    'notnull' => false
                ),
                "minutes_required" => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => false
                )
            );

            $this->getDB()->createTable(self::TABLE_SETTNGS, $fields);
        }

        if (!$this->getDB()->tableExists(self::TABLE_UNKNOWN_PARTICIPANTS)) {
            $fields = array(
                "id" => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                "obj_id" => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                "user_name" => array(
                    'type' => 'text',
                    'length' => 256,
                    'notnull' => true
                ),
                "email" => array(
                    'type' => 'text',
                    'length' => 128,
                    'notnull' => true
                ),
                "phone" => array(
                    'type' => 'text',
                    'length' => 128,
                    'notnull' => true
                ),
                "company" => array(
                    'type' => 'text',
                    'length' => 256,
                    'notnull' => true
                ),
                "minutes" => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                )
            );

            $this->getDB()->createTable(self::TABLE_UNKNOWN_PARTICIPANTS, $fields);
        }
    }

    /**
     * Create primary key for settings table
     *
     * @return void
     */
    public function createPrimaryKeySettings() : void
    {
        $this->getDB()->addPrimaryKey(static::TABLE_SETTNGS, array("obj_id"));
    }

    /**
     * Create primary key for participants table
     *
     * @return void
     */
    public function createPrimaryKeyParticipants() : void
    {
        $this->getDB()->addPrimaryKey(static::TABLE_UNKNOWN_PARTICIPANTS, array("id"));
    }

    /**
     * Create primary key for participants table
     *
     * @return void
     */
    public function createSequenceParticipants() : void
    {
        $this->getDB()->createSequence(static::TABLE_UNKNOWN_PARTICIPANTS);
    }

    /**
     * Update table with new column
     *
     * @return void
     */
    public function update1() : void
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_SETTNGS, 'upload_required')) {
            $this->getDB()->addTableColumn(self::TABLE_SETTNGS, 'upload_required', array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => false
            ));
        }
    }

    /**
     * Get intance of db
     *
     * @throws \Exception
     *
     * @return \ilDBInterface
     */
    protected function getDB() : \ilDBInterface
    {
        if (!$this->db) {
            throw new \Exception("no Database");
        }
        return $this->db;
    }

    /**
     * Get next id
     *
     * @param string 	$table
     *
     * @return int
     */
    protected function getNextId(string $table) : int
    {
        return (int) $this->getDB()->nextId($table);
    }
}
