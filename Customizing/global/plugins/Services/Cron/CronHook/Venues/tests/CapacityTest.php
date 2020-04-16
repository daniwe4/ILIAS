<?php

use \CaT\Plugins\Venues\ObjectFactory;
use \CaT\Plugins\Venues\Venues\Capacity\Capacity;
use PHPUnit\Framework\TestCase;

/**
 * Test the settings of Venue
 *
  * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class CapacityTest extends TestCase
{
    use ObjectFactory;

    public function testConstruction()
    {
        $id = 1;
        $number_rooms_overnight = 1;
        $min_person_any_room = 1;
        $max_person_any_room = 1;
        $min_room_size = 1;
        $max_room_size = 1;
        $room_count = 1;

        $cap = $this->getCapacityObject(
            $id,
            $number_rooms_overnight,
            $min_person_any_room,
            $max_person_any_room,
            $min_room_size,
            $max_room_size,
            $room_count
        );

        $this->assertInstanceOf(Capacity::class, $cap);

        $this->assertEquals($id, $cap->getId());
        $this->assertEquals($number_rooms_overnight, $cap->getNumberRoomsOvernights());
        $this->assertEquals($min_person_any_room, $cap->getMinPersonAnyRoom());
        $this->assertEquals($max_person_any_room, $cap->getMaxPersonAnyRoom());
        $this->assertEquals($min_room_size, $cap->getMinRoomSize());
        $this->assertEquals($max_room_size, $cap->getMaxRoomSize());
        $this->assertEquals($room_count, $cap->getRoomCount());
    }
}
