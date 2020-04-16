<?php

namespace CaT\Plugins\Webinar\Participant;

/**
 * Implementation only for booked participants based on ilias
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilDB implements DB
{
    const TABLE_NAME = "xwbr_participants";

    /**
     * @var \ilDBInterface
     */
    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @inhertidoc
     */
    public function book($obj_id, $user_id, $user_name, $minutes = null)
    {
        $values = array("obj_id" => array("integer", $obj_id),
            "user_id" => array("integer", $user_id),
            "user_name" => array("text", $user_name),
            "minutes" => array("integer", $minutes),
            "passed" => array("integer", null)
        );

        $this->getDB()->insert(self::TABLE_NAME, $values);
    }

    /**
     * @inhertidoc
     */
    public function cancel($obj_id, $user_id)
    {
        $query = "DELETE FROM " . self::TABLE_NAME . "\n"
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer") . "\n"
                . "     AND user_id = " . $this->getDB()->quote($user_id, "integer");

        $this->getDB()->manipulate($query);
    }

    /**
     * Check current user is booked
     *
     * @param int 	$obj_id
     * @param int 	$user_id
     *
     * @return bool
     */
    public function isBookedUser($obj_id, $user_id)
    {
        assert('is_int($obj_id)');
        assert('is_int($user_id)');

        $query = "SELECT COUNT(user_id) as cnt\n"
                . " FROM " . self::TABLE_NAME . "\n"
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer") . "\n"
                . "     AND user_id = " . $this->getDB()->quote($user_id, "integer");

        $res = $this->getDB()->query($query);
        $row = $this->getDB()->fetchAssoc($res);

        return $row["cnt"] > 0;
    }

    /**
     * Get ids of all booked users
     *
     * @param int 	$obj_id
     *
     * @return int[]
     */
    public function getAllBookedUserIds($obj_id)
    {
        assert('is_int($obj_id)');

        $query = "SELECT user_id" . PHP_EOL
                . " FROM " . self::TABLE_NAME . PHP_EOL
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");

        $res = $this->getDB()->query($query);
        $ret = array();

        while ($row = $this->getDB()->fetchAssoc($res)) {
            $ret[] = $row["user_id"];
        }

        return $ret;
    }

    /**
     * Create table for booked users
     *
     * @return null
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
                    'user_id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'user_name' => array(
                        'type' => 'text',
                        'length' => 80,
                        'notnull' => true
                    ),
                    'minutes' => array(
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
     * @return null
     */
    public function createPrimaryKey()
    {
        $this->getDB()->addPrimaryKey(self::TABLE_NAME, array("obj_id", "user_id"));
    }

    /**
     * Update table with new column
     *
     * @return null
     */
    public function tableUpdate1()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, 'passed')) {
            $this->getDB()->addTableColumn(self::TABLE_NAME, 'passed', array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => false
            ));
        }
    }

    /**
     * Get intance of db
     *
     * @throws \Exceptio
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
     * Get all user ids of completed users
     *
     * @param int 	$obj_id
     * @param int 	$passed_after
     *
     * @return int[]
     */
    public function getCompletedUser($obj_id, $passed_after)
    {
        $query = "SELECT user_id, minutes, passed\n"
                . " FROM " . self::TABLE_NAME . "\n"
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");

        $res = $this->getDB()->query($query);

        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($res)) {
            if ($row["passed"] === null && (int) $row["minutes"] >= $passed_after) {
                $ret[] = (int) $row["user_id"];
            } else {
                if ((bool) $row["passed"] == true) {
                    $ret[] = (int) $row["user_id"];
                }
            }
        }

        return $ret;
    }

    /**
     * Set participation status
     *
     * @param int 	$obj_id
     * @param int 	$user_id
     * @param bool 	$status
     *
     * @return null
     */
    public function setParticipationStatus($obj_id, $user_id, $status)
    {
        $where = array("obj_id" => array("integer", $obj_id),
            "user_id" => array("integer", $user_id)
        );

        $values = array("passed" => array("integer", $status));

        $this->getDB()->update(self::TABLE_NAME, $values, $where);
    }

    /**
     * Get all user ids of failed users
     *
     * @param int 	$obj_id
     * @param int 	$passed_after
     *
     * @return int[]
     */
    public function getFailedUser($obj_id, $passed_after)
    {
        $query = "SELECT user_id, minutes, passed\n"
                . " FROM " . self::TABLE_NAME . "\n"
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");

        $res = $this->getDB()->query($query);

        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($res)) {
            if ($row["passed"] === null && (int) $row["minutes"] < $passed_after) {
                $ret[] = (int) $row["user_id"];
            } else {
                if ((bool) $row["passed"] == false) {
                    $ret[] = (int) $row["user_id"];
                }
            }
        }

        return $ret;
    }

    /**
     * Get Minutes for user id
     *
     * @param int 	$obj_id
     * @param int 	$user_id
     *
     * @return string[] | null
     */
    public function getLPDataFor($obj_id, $user_id)
    {
        $select = "SELECT minutes, passed"
                . " FROM " . self::TABLE_NAME
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer")
                . "   AND user_id = " . $this->getDB()->quote($user_id, "integer");

        $ret = array();
        $res = $this->getDB()->query($select);
        if ($this->getDB()->numRows($res) == 0) {
            return null;
        }

        $row = $this->getDB()->fetchAssoc($res);

        return $row;
    }

    /**
     * Deletes all known participants
     *
     * @param int 	$obj_id
     *
     * @return null
     */
    public function deleteFor($obj_id)
    {
        $query = "DELETE FROM " . self::TABLE_NAME . "\n"
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");

        $this->getDB()->manipulate($query);
    }
}
