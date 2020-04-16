<?php

namespace CaT\Plugins\BookingModalities\Settings\Waitinglist;

/**
 * Interface for DB handle of additional setting values
 */
class ilDB implements DB
{
    const TABLE_BOOKING = "xbkm_booking";
    const TABLE_MEMBER = "xbkm_member";
    const TABLE_STORNO = "xbkm_storno";
    const TABLE_WAITINGLIST = "xbkm_waitinglist";
    const TABLE_APPROVERS = "xbkm_approvers";

    const TABLE_ROLES = "xbkm_roles";

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
        $waitinglist = new Waitinglist($obj_id, $cancellation, $max, $modus);
        $values = array("obj_id" => array("int", $waitinglist->getObjId()));
        $this->getDB()->insert(self::TABLE_WAITINGLIST, $values);
        return $waitinglist;
    }

    /**
     * @inheritdoc
     */
    public function update(Waitinglist $waitinglist_settings)
    {
        $where = array("obj_id" => array("int", $waitinglist_settings->getObjId()));

        $values = array("cancellation" => array("int", $waitinglist_settings->getCancellation()),
            "max" => array("int", $waitinglist_settings->getMax()),
            "modus" => array("text", $waitinglist_settings->getModus())
        );

        $this->getDB()->update(self::TABLE_WAITINGLIST, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function selectFor($obj_id)
    {
        assert('is_int($obj_id)');
        $query = "SELECT cancellation, max, modus\n"
                . " FROM " . self::TABLE_WAITINGLIST . " \n"
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");

        $res = $this->getDB()->query($query);

        if ($this->getDB()->numRows($res) == 0) {
            return new Waitinglist($obj_id);
        }

        $row = $this->getDB()->fetchAssoc($res);
        $cancellation = $row["cancellation"];
        if ($cancellation !== null) {
            $cancellation = (int) $cancellation;
        }

        $max = $row["max"];
        if ($max !== null) {
            $max = (int) $max;
        }
        return new Waitinglist($obj_id, $cancellation, $max, $row["modus"]);
    }

    /**
     * @inheritdoc
     */
    public function deleteFor($obj_id)
    {
        assert('is_int($obj_id)');
        $query = "DELETE FROM " . self::TABLE_WAITINGLIST . "\n"
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
        if (!$this->getDB()->tableExists(self::TABLE_WAITINGLIST)) {
            $fields =
                array('obj_id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'cancellation' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => false
                    ),
                    'max' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => false
                    ),
                    'modus' => array(
                        'type' => 'text',
                        'length' => 32,
                        'notnull' => false
                    )
                );

            $this->getDB()->createTable(self::TABLE_WAITINGLIST, $fields);
        }
    }

    /**
     * Create primary key for waitinglist
     *
     * @return null
     */
    public function createWaitinglistPrimaryKey()
    {
        $this->getDB()->addPrimaryKey(self::TABLE_WAITINGLIST, array("obj_id"));
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
