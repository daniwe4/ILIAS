<?php

namespace CaT\Plugins\RoomSetup\Equipment;

/**
 * Guidleline for db communication
 */
interface DB
{
    /**
     * Install tables or something
     *
     * @return null
     */
    public function install();

    /**
     * Create a new equipment entry
     *
     * @param int 		$obj_id
     * @param int[]		$service_options
     * @param string 	$special_wishes
     * @param string 	$room_information
     * @param string 	$seat_order
     *
     * @return Equipment
     */
    public function create($obj_id, array $service_options, $special_wishes, $room_information, $seat_order);

    /**
     * Update an existing equipment object
     *
     * @param Equipment 	$equipment
     *
     * @return null
     */
    public function update(Equipment $equipment);

    /**
     * Get equipment for obj id
     *
     * @param int 		$obj_id
     *
     * @throws \LogicException 	if no data found for obj id
     *
     * @return Equipment
     */
    public function selectFor($obj_id);

    /**
     * Delete equipment for obj_id
     *
     * @param int 		$obj_id
     *
     * @return null
     */
    public function deleteFor($obj_id);

    /**
     * Allocate service option to room setup
     *
     * @param int 		$obj_id
     * @param int 		$service_option
     *
     * @return null
     */
    public function allocateServiceOption($obj_id, $service_option);

    /**
     * Deallocate service option to room setup
     *
     * @param int 		$obj_id
     * @param int 		$service_option
     *
     * @return null
     */
    public function deallocateServiceOption($obj_id, $service_option);

    /**
     * Deallocate all service options
     *
     * @param int 		$obj_id
     *
     * @return null
     */
    public function deallocateAllServiceOptions($obj_id);
}
