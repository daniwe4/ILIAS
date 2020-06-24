<?php

namespace CaT\Plugins\CourseMember\Members;

require_once("Services/Calendar/classes/class.ilDateTime.php");
require_once("Services/User/classes/class.ilObjUser.php");

/**
 * Implementation of DB inteface for ILIAS
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilDB implements DB
{
    const TABLE_NAME = "xcmb_members";
    const TABLE_USR = "usr_data";

    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var ilObjUser
     */
    protected $current_user;

    public function __construct(\ilDBInterface $db, \ilObjUser $current_user)
    {
        $this->db = $db;
        $this->current_user = $current_user;
    }

    /**
     * @inheritdoc
     */
    public function upsert(Member $member)
    {
        $dt = new \ilDateTime(time(), IL_CAL_UNIX);
        $member = $member->withLastEdited($dt)
            ->withLastEditBy((int) $this->current_user->getId());

        $primaryKeys = array("user_id" => array("integer", $member->getUserId()),
            "crs_id" => array("integer", $member->getCrsId())
        );

        $columns = array("lp_id" => array("integer", $member->getLPId()),
            "lp_value" => array("text", $member->getLPValue()),
            "ilias_lp" => array("integer", $member->getILIASLP()),
            "credits" => array("float", $member->getCredits()),
            "last_edited" => array("text",$member->getLastEdited()->get(IL_CAL_DATETIME)),
            "last_edit_by" => array("integer", $member->getLastEditBy()),
            "idd_learning_time" => array("integer", $member->getIDDLearningTime())
        );

        $this->getDB()->replace(self::TABLE_NAME, $primaryKeys, $columns);

        return $member;
    }

    /**
     * @inheritdoc
     */
    public function select(int $crs_id)
    {
        $query = "SELECT mem.user_id, mem.crs_id, mem.lp_id, mem.lp_value, mem.ilias_lp," . PHP_EOL
                . " mem.credits, mem.last_edited, mem.last_edit_by, mem.idd_learning_time," . PHP_EOL
                . " usr.firstname, usr.lastname, usr.login" . PHP_EOL
                . " FROM " . self::TABLE_NAME . " mem" . PHP_EOL
                . " JOIN " . self::TABLE_USR . " usr" . PHP_EOL
                . "     ON mem.user_id = usr.usr_id" . PHP_EOL
                . " WHERE crs_id = " . $this->getDB()->quote($crs_id, "integer");

        $ret = array();
        $res = $this->getDB()->query($query);
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $user_id = (int) $row["user_id"];

            $dt = $row["last_edited"];
            if ($dt !== null) {
                $dt = new \ilDateTime($dt, IL_CAL_DATETIME);
            }

            $credits = $row["credits"];
            if ($credits !== null) {
                $credits = (float) $credits;
            }

            $lp_id = $row["lp_id"];
            if ($lp_id !== null) {
                $lp_id = (int) $lp_id;
            }

            $idd_learning_time = $row["idd_learning_time"];
            if ($idd_learning_time !== null) {
                $idd_learning_time = (int) $idd_learning_time;
            }

            $mem = $this->getMember(
                $user_id,
                $crs_id,
                $lp_id,
                $row["lp_value"],
                (int) $row["ilias_lp"],
                $credits,
                $idd_learning_time,
                $dt,
                (int) $row["last_edit_by"]
            );

            $ret[] = $mem
                ->withFirstname($row["firstname"])
                ->withLastname($row["lastname"])
                ->withLogin($row["login"]);
        }

        return $ret;
    }

    /**
     * Get an member object with
     *
     * @param int 	$user_id
     * @param int 	$crs_id
     * @param string | null 	$lp_value
     * @param int | null 	$ilias_lp
     * @param float | null 	$credits
     * @param int | null 	$idd_learning_time
     * @param \ilDateTime | null 	$last_edited
     * @param int | null	$last_edit_by
     *
     * @return Member
     */
    public function getMember(
        int $user_id,
        int $crs_id,
        ?int $lp_id,
        ?string $lp_value,
        ?int $ilias_lp,
        ?float $credits,
        ?int $idd_learning_time = null,
        ?\ilDateTime $last_edited = null,
        ?int $last_edit_by = null
    ) {
        return new Member(
            $user_id,
            $crs_id,
            $lp_id,
            $lp_value,
            $ilias_lp,
            $credits,
            $idd_learning_time,
            $last_edited,
            $last_edit_by
        );
    }

    /**
     * @inheritdoc
     */
    public function deleteForUserAndCourse(int $user_id, int $crs_id)
    {
        $query = "DELETE FROM " . self::TABLE_NAME . PHP_EOL
                . " WHERE user_id = " . $this->getDB()->quote($user_id, "integer") . PHP_EOL
                . "     AND crs_id = " . $this->getDB()->quote($crs_id, "integer");

        $this->getDB()->manipulate($query);
    }

    /**
     * @inheritdoc
     */
    public function deleteForCourse(int $crs_id)
    {
        $query = "DELETE FROM " . self::TABLE_NAME . PHP_EOL
                . " WHERE crs_id = " . $this->getDB()->quote($crs_id, "integer");

        $this->getDB()->manipulate($query);
    }

    /**
     * Get all user ids of completed users
     *
     * @param int 	$obj_id
     * @param int 	$lp_status
     *
     * @return int[]
     */
    public function getUserIdsWithLPStatus(int $obj_id, int $lp_status)
    {
        $query = "SELECT user_id " . PHP_EOL
                . " FROM " . self::TABLE_NAME . PHP_EOL
                . " WHERE crs_id = " . $this->getDB()->quote($obj_id, "integer") . PHP_EOL
                . "    AND ilias_lp = " . $this->getDB()->quote($lp_status, "integer");

        $ret = array();
        $res = $this->getDB()->query($query);
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $ret[] = (int) $row["user_id"];
        }

        return $ret;
    }

    /**
     * Get LP Status for user id
     *
     * @param int 	$obj_id
     * @param int 	$user_id
     *
     * @return string[] | null
     */
    public function getLPDataFor($obj_id, $user_id)
    {
        $query = "SELECT ilias_lp"
                . " FROM " . self::TABLE_NAME
                . " WHERE crs_id = " . $this->getDB()->quote($obj_id, "integer")
                . "   AND user_id = " . $this->getDB()->quote($user_id, "integer");

        $ret = array();
        $res = $this->getDB()->query($query);
        if ($this->getDB()->numRows($res) == 0) {
            return null;
        }

        $row = $this->getDB()->fetchAssoc($res);

        return $row["ilias_lp"];
    }

    /**
     * Get the idd learning time for single user
     *
     * @param int 	$obj_id
     * @param int 	$user_id
     *
     * @return int
     */
    public function getMinutesFor(int $obj_id, int $user_id)
    {
        $query = "SELECT idd_learning_time" . PHP_EOL
                . " FROM " . self::TABLE_NAME . PHP_EOL
                . " WHERE crs_id = " . $this->getDB()->quote($obj_id, "integer") . PHP_EOL
                . "   AND user_id = " . $this->getDB()->quote($user_id, "integer");

        $ret = array();
        $res = $this->getDB()->query($query);
        if ($this->getDB()->numRows($res) == 0) {
            return 0;
        }

        $row = $this->getDB()->fetchAssoc($res);

        return (int) $row["idd_learning_time"];
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
                array('user_id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'crs_id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'lp_value' => array(
                        'type' => 'text',
                        'length' => 128,
                        'notnull' => true
                    ),
                    'ilias_lp' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'last_edited' => array(
                        'type' => 'timestamp',
                        'notnull' => true
                    ),
                    'last_edit_by' => array(
                        'type' => 'integer',
                        'length' => 4,
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
        if (!$this->getDB()->indexExistsByFields(self::TABLE_NAME, array("user_id", "crs_id"))) {
            $this->getDB()->addPrimaryKey(self::TABLE_NAME, array("user_id", "crs_id"));
        }
    }

    /**
     * Updates table columns
     *
     * @return void
     */
    public function update1()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "credits")) {
            $field = array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => false
            );

            $this->getDB()->addTableColumn(self::TABLE_NAME, "credits", $field);
        }
    }

    /**
     * Updates table columns
     *
     * @return void
     */
    public function update2()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "lp_id")) {
            $field = array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => false
            );

            $this->getDB()->addTableColumn(self::TABLE_NAME, "lp_id", $field);
        }
    }

    /**
     * Updates table columns
     *
     * @return void
     */
    public function update3()
    {
        if ($this->getDB()->tableColumnExists(self::TABLE_NAME, "lp_value")) {
            $field = array(
                'type' => 'text',
                'length' => 128,
                'notnull' => false
            );

            $this->getDB()->modifyTableColumn(self::TABLE_NAME, "lp_value", $field);
        }

        if ($this->getDB()->tableColumnExists(self::TABLE_NAME, "ilias_lp")) {
            $field = array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => false
            );

            $this->getDB()->modifyTableColumn(self::TABLE_NAME, "ilias_lp", $field);
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
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "idd_learning_time")) {
            $field = array(
                'type' => 'integer',
                'length' => 8,
                'notnull' => false
            );

            $this->getDB()->addTableColumn(self::TABLE_NAME, "idd_learning_time", $field);
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
