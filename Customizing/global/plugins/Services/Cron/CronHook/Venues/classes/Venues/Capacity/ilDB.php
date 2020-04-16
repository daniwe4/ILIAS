<?php

declare(strict_types=1);

namespace CaT\Plugins\Venues\Venues\Capacity;

use \CaT\Plugins\Venues\ObjectFactory;

/**
 * Implementation of DB inteface for capacity configuration
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilDB implements DB
{
    use ObjectFactory;

    const TABLE_NAME = "venues_capacity";

    /**
     * @var \ilDBInterface
     */
    protected $db = null;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function create(
        int $id,
        int $number_rooms_overnight = null,
        int $min_person_any_room = null,
        int $max_person_any_room = null,
        int $min_room_size = null,
        int $max_room_size = null,
        int $room_count = null
    ) : Capacity {
        $capacity = new Capacity($id, $number_rooms_overnight, $min_person_any_room, $max_person_any_room, $min_room_size, $max_room_size, $room_count);

        $values = array("id" => array("integer", $capacity->getId()),
                "number_rooms_overnight" => array("integer", $capacity->getNumberRoomsOvernights()),
                "min_person_any_room" => array("integer", $capacity->getMinPersonAnyRoom()),
                "max_person_any_room" => array("integer", $capacity->getMaxPersonAnyRoom()),
                "min_room_size" => array("integer", $capacity->getMinRoomSize()),
                "max_room_size" => array("integer", $capacity->getMaxRoomSize()),
                "room_count" => array("integer", $capacity->getRoomCount())
            );

        $this->db->insert(self::TABLE_NAME, $values);

        return $capacity;
    }

    /**
     * @inheritdoc
     */
    public function update(Capacity $capacity)
    {
        $where = array("id" => array("integer", $capacity->getId()));

        $values = array("number_rooms_overnight" => array("integer", $capacity->getNumberRoomsOvernights()),
                "min_person_any_room" => array("integer", $capacity->getMinPersonAnyRoom()),
                "max_person_any_room" => array("integer", $capacity->getMaxPersonAnyRoom()),
                "min_room_size" => array("integer", $capacity->getMinRoomSize()),
                "max_room_size" => array("integer", $capacity->getMaxRoomSize()),
                "room_count" => array("integer", $capacity->getRoomCount())
            );

        $this->db->update(self::TABLE_NAME, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function delete(int $id)
    {
        $query = "DELETE FROM " . self::TABLE_NAME . "\n"
                . " WHERE id = " . $this->db->quote($id, "integer");

        $this->db->manipulate($query);
    }

    /**
     * Create the table for capacity configuration
     *
     * @return null
     */
    public function createTable()
    {
        if (!$this->db->tableExists(self::TABLE_NAME)) {
            $fields = array(
                    "id" => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    "number_rooms" => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => false
                    ),
                    "min_person_any_room" => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => false
                    ),
                    "max_person_any_room" => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => false
                    ),
                    "min_room_size" => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => false
                    ),
                    "max_room_size" => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => false
                    ),
                );


            $this->db->createTable(self::TABLE_NAME, $fields);
        }
    }

    /**
     * Create the primary key for table
     *
     * @return null
     */
    public function createPrimary()
    {
        $this->db->addPrimaryKey(self::TABLE_NAME, array("id"));
    }

    /**
     * Update table
     *
     * @return null
     */
    public function update1()
    {
        if ($this->db->tableColumnExists(self::TABLE_NAME, "number_rooms")) {
            $this->db->renameTableColumn(self::TABLE_NAME, "number_rooms", "number_rooms_overnight");
        }

        if (!$this->db->tableColumnExists(self::TABLE_NAME, "room_count")) {
            $fields = array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => false
                    );

            $this->db->addTableColumn(self::TABLE_NAME, "room_count", $fields);
        }
    }
}
