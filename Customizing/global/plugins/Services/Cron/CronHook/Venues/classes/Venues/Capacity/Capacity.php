<?php

declare(strict_types=1);

namespace CaT\Plugins\Venues\Venues\Capacity;

/**
 * Venue configuration entries for capacity settings
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class Capacity
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int | null
     */
    protected $number_rooms_overnight;

    /**
     * @var int | null
     */
    protected $min_person_any_room;

    /**
     * @var int | null
     */
    protected $max_person_any_room;

    /**
     * @var int | null
     */
    protected $min_room_size;

    /**
     * @var int | null
     */
    protected $max_room_size;

    /**
     * @var int | null
     */
    protected $room_count;

    /**
     * @param int 	$id
     * @param int | null 	$number_rooms_overnight
     * @param int | null 	$min_person_any_room
     * @param int | null 	$max_person_any_room
     * @param int | null 	$min_room_size
     * @param int | null 	$max_room_size
     * @param int | null 	$room_count
     */
    public function __construct(
        int $id,
        int $number_rooms_overnight = null,
        int $min_person_any_room = null,
        int $max_person_any_room = null,
        int $min_room_size = null,
        int $max_room_size = null,
        int $room_count = null
    ) {
        $this->id = $id;
        $this->number_rooms_overnight = $number_rooms_overnight;
        $this->min_person_any_room = $min_person_any_room;
        $this->max_person_any_room = $max_person_any_room;
        $this->min_room_size = $min_room_size;
        $this->max_room_size = $max_room_size;
        $this->room_count = $room_count;
    }

    public function getId() : int
    {
        return $this->id;
    }

    /**
     * @return int | null
     */
    public function getNumberRoomsOvernights()
    {
        return $this->number_rooms_overnight;
    }

    /**
     * @return int | null
     */
    public function getMinPersonAnyRoom()
    {
        return $this->min_person_any_room;
    }

    /**
     * @return int | null
     */
    public function getMaxPersonAnyRoom()
    {
        return $this->max_person_any_room;
    }

    /**
     * @return int | null
     */
    public function getMinRoomSize()
    {
        return $this->min_room_size;
    }

    /**
     * @return int | null
     */
    public function getMaxRoomSize()
    {
        return $this->max_room_size;
    }

    /**
     * @return int | null
     */
    public function getRoomCount()
    {
        return $this->room_count;
    }
}
