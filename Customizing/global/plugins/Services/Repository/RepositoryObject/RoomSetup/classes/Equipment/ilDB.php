<?php

namespace CaT\Plugins\RoomSetup\Equipment;

/**
 * ILIAS implementation of DB interface for Equipment
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilDB implements DB
{
    const TABLE_NAME = "xrse_equipment";
    const TABLE_EQUIPMENT_SO = "xrse_equipment_so";

    /**
     * @var /ilDBInterface
     */
    protected $db;

    public function __construct(/*ilDBInterface*/ $db)
    {
        $this->db = $db;
    }

    /**
     * @inhertidoc
     */
    public function install()
    {
        $this->createTables();
    }

    /**
     * @inheritdoc
     */
    public function create($obj_id, array $service_options, $special_wishes, $room_information, $seat_order)
    {
        assert('is_int($obj_id)');
        assert('is_string($special_wishes)');
        assert('is_string($room_information)');
        assert('is_string($seat_order)');

        $equipment = new Equipment($obj_id, $service_options, $special_wishes, $room_information, $seat_order);

        $values = array(
            "obj_id" => array("integer", $equipment->getObjId()),
            "special_wishes" => array("text", $equipment->getSpecialWishes()),
            "room_information" => array("text", $equipment->getRoomInformation()),
            "seat_order" => array("text", $equipment->getSeatOrder())
            );

        foreach ($service_options as $key => $service_option) {
            $this->allocateServiceOption($obj_id, $service_option);
        }

        $this->getDB()->insert(self::TABLE_NAME, $values);

        return $equipment;
    }

    /**
     * @inheritdoc
     */
    public function update(Equipment $equipment)
    {
        $obj_id = $equipment->getObjId();
        $where = array("obj_id" => array("integer", $obj_id));

        $values = array(
            "special_wishes" => array("text", $equipment->getSpecialWishes()),
            "room_information" => array("text", $equipment->getRoomInformation()),
            "seat_order" => array("text", $equipment->getSeatOrder())
            );

        $this->deallocateAllServiceOptions($obj_id);
        foreach ($equipment->getServiceOptions() as $key => $service_option) {
            $this->allocateServiceOption($obj_id, $service_option);
        }

        $this->getDB()->update(self::TABLE_NAME, $values, $where);

        return $equipment;
    }

    /**
     * @inheritdoc
     */
    public function selectFor($obj_id)
    {
        assert('is_int($obj_id)');

        $query = "SELECT base.obj_id, GROUP_CONCAT(allocated.service_option_id SEPARATOR ',') as service_options, base.special_wishes, base.room_information, base.seat_order\n"
                . " FROM " . self::TABLE_NAME . " AS base\n"
                . " LEFT JOIN " . self::TABLE_EQUIPMENT_SO . " AS allocated\n"
                . "    ON base.obj_id = allocated.obj_id\n"
                . " WHERE base.obj_id = " . $this->getDB()->quote($obj_id, "integer") . "\n"
                . " GROUP BY base.obj_id";

        $res = $this->getDB()->query($query);

        if ($this->getDB()->numRows($res) == 0) {
            throw new \LogicException(__METHOD__ . " no equipment found for obj_id " . $obj_id);
        }

        $row = $this->getDB()->fetchAssoc($res);

        $service_options = array();
        if ($row["service_options"] !== null) {
            $service_options = explode(",", $row["service_options"]);
        }
        return new Equipment((int) $row["obj_id"], $service_options, $row["special_wishes"], $row["room_information"], $row["seat_order"]);
    }

    /**
     * @inheritdoc
     */
    public function deleteFor($obj_id)
    {
        assert('is_int($obj_id)');

        $query = "DELETE FROM " . SELF::TABLE_NAME . "\n"
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");

        $this->deallocateAllServiceOptions($obj_id);

        $this->getDB()->manipulate($query);
    }

    /**
     * @inheritdoc
     */
    public function allocateServiceOption($obj_id, $service_option)
    {
        $values = array("obj_id" => array("integer", $obj_id)
                      , "service_option_id" => array("integer", $service_option)
            );

        $this->getDB()->insert(self::TABLE_EQUIPMENT_SO, $values);
    }

    /**
     * @inheritdoc
     */
    public function deallocateServiceOption($obj_id, $service_option)
    {
        $query = "DELETE FROM " . self::TABLE_EQUIPMENT_SO . "\n"
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer") . "\n"
                . "     AND service_option_id = " . $this->getDB()->quote($service_option, "integer");

        $this->getDB()->manipulate($query);
    }

    /**
     * @inheritdoc
     */
    public function deallocateAllServiceOptions($obj_id)
    {
        $query = "DELETE FROM " . self::TABLE_EQUIPMENT_SO . "\n"
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");

        $this->getDB()->manipulate($query);
    }

    /**
     * Create Tabeles for equipment
     *
     * @return null
     */
    protected function createTables()
    {
        if (!$this->getDB()->tableExists(self::TABLE_NAME)) {
            $fields =
                array('obj_id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'room_information' => array(
                        'type' => 'clob'
                    ),
                    'seat_order' => array(
                        'type' => 'clob'
                    )
                );
            $this->getDB()->createTable(self::TABLE_NAME, $fields);
            $this->getDB()->addPrimaryKey(self::TABLE_NAME, array("obj_id"));
        }

        if (!$this->getDB()->tableExists(self::TABLE_EQUIPMENT_SO)) {
            $fields =
                array('obj_id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'service_option_id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    )
                );
            $this->getDB()->createTable(self::TABLE_EQUIPMENT_SO, $fields);
            $this->getDB()->addPrimaryKey(self::TABLE_EQUIPMENT_SO, array("obj_id", "service_option_id"));
        }
    }

    /**
     * @inheritdoc
     */
    protected function getDB()
    {
        if (!$this->db) {
            throw new \Exception("no Database");
        }
        return $this->db;
    }

    public function getParentRefIdFromTree($obj_id)
    {
        $query = "SELECT parent FROM tree WHERE child = " . $obj_id;
        $res = $this->getDB()->query($query);

        if ($this->getDB()->numRows($res) == 0) {
            return null;
        }

        $row = $this->getDB()->fetchAssoc($res);

        return $row["parent"];
    }

    /**
     * Update1
     */
    public function update1()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, 'special_wishes')) {
            $this->getDB()->addTableColumn(
                self::TABLE_NAME,
                'special_wishes',
                array(
                    'type' => 'clob'
                )
            );
        }
    }
}
