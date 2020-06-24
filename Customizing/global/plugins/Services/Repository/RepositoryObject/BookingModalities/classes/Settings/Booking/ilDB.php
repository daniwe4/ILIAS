<?php

namespace CaT\Plugins\BookingModalities\Settings\Booking;

use CaT\Plugins\BookingModalities\Settings\ApproveRole\ApproveRole;

/**
 * Interface for DB handle of additional setting values
 */
class ilDB implements DB
{
    const TABLE_BOOKING = "xbkm_booking";
    const TABLE_APPROVERS = "xbkm_approvers";

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
    public function create(int $obj_id)
    {
        $booking = new Booking($obj_id);
        $values = array("obj_id" => array("int", $booking->getObjId()));
        $this->getDB()->insert(self::TABLE_BOOKING, $values);
        return $booking;
    }

    /**
     * @inheritdoc
     */
    public function update(Booking $booking_settings)
    {
        $where = array("obj_id" => array("int", $booking_settings->getObjId()));

        $values = array("beginning" => array("int", $booking_settings->getBeginning()),
            "deadline" => array("int", $booking_settings->getDeadline()),
            "modus" => array("text", $booking_settings->getModus()),
            "skip_duplicate_check" => array("text", $booking_settings->getSkipDuplicateCheck()),
            "hide_superior_approve" => array("text", $booking_settings->getHideSuperiorApprove()),
            "to_be_acknowledged" => array("integer", $booking_settings->getToBeAcknowledged())
        );

        $this->getDB()->update(self::TABLE_BOOKING, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function selectFor(int $obj_id)
    {
        $query = "SELECT" . PHP_EOL
                . "A.beginning," . PHP_EOL
                . "A.deadline," . PHP_EOL
                . "A.modus," . PHP_EOL
                . "A.skip_duplicate_check," . PHP_EOL
                . "A.hide_superior_approve," . PHP_EOL
                . "A.to_be_acknowledged," . PHP_EOL
                . "B.obj_id AS approv_objid," . PHP_EOL
                . "B.role," . PHP_EOL
                . "B.position," . PHP_EOL
                . "B.parent" . PHP_EOL
                . " FROM " . self::TABLE_BOOKING . " A" . PHP_EOL
                . " LEFT JOIN " . self::TABLE_APPROVERS . " B" . PHP_EOL
                . "     ON A.obj_id = B.obj_id" . PHP_EOL
                . "         AND B.parent = 'booking'" . PHP_EOL
                . " WHERE A.obj_id = " . $this->getDB()->quote($obj_id, "integer") . PHP_EOL
                . " ORDER BY B.position";

        $res = $this->getDB()->query($query);

        if ($this->getDB()->numRows($res) == 0) {
            return new Booking($obj_id);
        }

        $booking = null;
        $approve_roles = [];
        while ($row = $this->getDB()->fetchAssoc($res)) {
            if ($booking === null) {
                $beginning = $row["beginning"];
                if ($beginning !== null) {
                    $beginning = (int) $beginning;
                }
                $deadline = $row["deadline"];
                if ($deadline !== null) {
                    $deadline = (int) $deadline;
                }
                $booking = new Booking(
                    $obj_id,
                    $beginning,
                    $deadline,
                    $row["modus"],
                    array(),
                    (bool) $row["to_be_acknowledged"],
                    (bool) $row["skip_duplicate_check"],
                    (bool) $row["hide_superior_approve"]
                );
            }

            if ($row["approv_objid"] !== null) {
                $approve_roles[] = new ApproveRole($obj_id, $row["parent"], (int) $row["position"], (int) $row["role"]);
            }
        }

        return $booking->withApproveRoles($approve_roles);
    }

    /**
     * @inheritdoc
     */
    public function deleteFor($obj_id)
    {
        $query = "DELETE FROM " . self::TABLE_BOOKING . "\n"
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
        if (!$this->getDB()->tableExists(self::TABLE_BOOKING)) {
            $fields =
                array('obj_id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'beginning' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => false
                    ),
                    'deadline' => array(
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

            $this->getDB()->createTable(self::TABLE_BOOKING, $fields);
        }
    }

    /**
     * Create primary key for booking
     *
     * @return null
     */
    public function createBookingPrimaryKey()
    {
        $this->getDB()->addPrimaryKey(self::TABLE_BOOKING, array("obj_id"));
    }

    /**
     * Update step 1
     *
     * @return void
     */
    public function update1()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_BOOKING, "skip_duplicate_check")) {
            $field = array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => false
            );
            $this->getDB()->addTableColumn(self::TABLE_BOOKING, "skip_duplicate_check", $field);
        }
    }

    /**
     * Update step 1
     *
     * @return void
     */
    public function update2()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_BOOKING, "hide_superior_approve")) {
            $field = array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => false
            );
            $this->getDB()->addTableColumn(self::TABLE_BOOKING, "hide_superior_approve", $field);

            $query = "UPDATE " . self::TABLE_BOOKING . " SET hide_superior_approve = 0";
            $this->getDB()->manipulate($query);
        }
    }


    /**
     * Update step 1
     *
     * @return void
     */
    public function update3()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_BOOKING, "to_be_acknowledged")) {
            $field = array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => false
            );
            $this->getDB()->addTableColumn(self::TABLE_BOOKING, "to_be_acknowledged", $field);

            $query = "UPDATE " . self::TABLE_BOOKING . " SET to_be_acknowledged = 1";
            $this->getDB()->manipulate($query);
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
