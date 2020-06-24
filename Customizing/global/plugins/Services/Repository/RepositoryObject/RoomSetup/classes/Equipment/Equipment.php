<?php

namespace CaT\Plugins\RoomSetup\Equipment;

/**
 * Object for the equipment of room setup
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-anf-training.de>
 */
class Equipment
{
    /**
     * Id of the room setup
     *
     * @var int
     */
    protected $obj_id;

    /**
     * @var int[] | []
     */
    protected $service_options;

    /**
     * @var string
     */
    protected $special_wishes;

    /**
     * @var string
     */
    protected $room_information;

    /**
     * @var string
     */
    protected $seat_order;

    /**
     * @param int 		$obj_id
     * @param int[]		$service_options
     * @param string 	$special_wishes
     * @param string 	$room_information
     * @param string 	$seat_order
     */
    public function __construct(
        int $obj_id,
        array $service_options = array(),
        string $special_wishes = "",
        string $room_information = "",
        string $seat_order = ""
    ) {
        $this->obj_id = $obj_id;
        $this->service_options = $service_options;
        $this->special_wishes = $special_wishes;
        $this->room_information = $room_information;
        $this->seat_order = $seat_order;
    }

    /**
     * Get the obj id of room setup
     *
     * @return int
     */
    public function getObjId()
    {
        return $this->obj_id;
    }

    /**
     * Get service options ids
     *
     * @return int[]
     */
    public function getServiceOptions()
    {
        return $this->service_options;
    }

    /**
     * Get special wishes
     *
     * @return string
     */
    public function getSpecialWishes()
    {
        return $this->special_wishes;
    }

    /**
     * Get information about the room
     *
     * @return string
     */
    public function getRoomInformation()
    {
        return $this->room_information;
    }

    /**
     * Get information about the seat order
     *
     * @return string
     */
    public function getSeatOrder()
    {
        return $this->seat_order;
    }

    /**
     * Get a cloned object with service options
     *
     * @param int[] 	$service_options
     *
     * @return Equipment
     */
    public function withServiceOptions(array $service_options)
    {
        $clone = clone $this;
        $clone->service_options = $service_options;
        return $clone;
    }

    /**
     * Get a cloned object with special wishes
     *
     * @param 	string
     * @return 	Equipment
     */
    public function withSpecialWishes($special_wishes)
    {
        $clone = clone $this;
        $clone->special_wishes = $special_wishes;
        return $clone;
    }

    /**
     * Get a cloned object with room information
     *
     * @param string 	$room_information
     *
     * @return Equipment
     */
    public function withRoomInformation(string $room_information)
    {
        $clone = clone $this;
        $clone->room_information = $room_information;
        return $clone;
    }

    /**
     * Get a cloned object with seat order
     *
     * @param string 	$seat_order
     *
     * @return Equipment
     */
    public function withSeatOrder(string $seat_order)
    {
        $clone = clone $this;
        $clone->seat_order = $seat_order;
        return $clone;
    }
}
