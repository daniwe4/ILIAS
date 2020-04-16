<?php

namespace CaT\Plugins\BookingModalities\Settings\Member;

/**
 * Interface for DB handle of additional setting values
 */
class ilDB implements DB
{
    const TABLE_MEMBER = "xbkm_member";

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
    public function create($obj_id)
    {
        assert('is_int($obj_id)');
        $member = new Member($obj_id);
        $values = array("obj_id" => array("int", $member->getObjId()));
        $this->getDB()->insert(self::TABLE_MEMBER, $values);
        return $member;
    }

    /**
     * @inheritdoc
     */
    public function update(Member $member_settings)
    {
        $where = array("obj_id" => array("int", $member_settings->getObjId()));

        $values = array("min" => array("int", $member_settings->getMin()),
            "max" => array("int", $member_settings->getMax())
        );

        $this->getDB()->update(self::TABLE_MEMBER, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function selectFor($obj_id)
    {
        assert('is_int($obj_id)');
        $query = "SELECT min, max\n"
                . " FROM " . self::TABLE_MEMBER . " \n"
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");

        $res = $this->getDB()->query($query);

        if ($this->getDB()->numRows($res) == 0) {
            return new Member($obj_id);
        }

        $row = $this->getDB()->fetchAssoc($res);
        $min = $row["min"];
        if ($min !== null) {
            $min = (int) $min;
        }

        $max = $row["max"];
        if ($max !== null) {
            $max = (int) $max;
        }

        return new Member($obj_id, $min, $max);
    }

    /**
     * @inheritdoc
     */
    public function deleteFor($obj_id)
    {
        assert('is_int($obj_id)');
        $query = "DELETE FROM " . self::TABLE_MEMBER . "\n"
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");
        $this->getDB()->manipulate($query);
    }

    /**
     * Creates tables for this plugin
     *
     * @return null
     */
    public function createTable1()
    {
        if (!$this->getDB()->tableExists(self::TABLE_MEMBER)) {
            $fields =
                array('obj_id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'min' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => false
                    ),
                    'max' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => false
                    )
                );

            $this->getDB()->createTable(self::TABLE_MEMBER, $fields);
        }
    }

    /**
     * Create primary key for member
     *
     * @return null
     */
    public function createMemberPrimaryKey()
    {
        $this->getDB()->addPrimaryKey(self::TABLE_MEMBER, array("obj_id"));
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
