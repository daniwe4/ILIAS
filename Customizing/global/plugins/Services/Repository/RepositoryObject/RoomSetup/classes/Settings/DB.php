<?php

namespace CaT\Plugins\RoomSetup\Settings;

/**
 * Interace for extendes settings db
 */
interface DB
{
    /**
     * Create new extended settings entries
     *
     * @param int 			$obj_id
     *
     * @return RoomSetup[]
     */
    public function create(int $obj_id);

    /**
     * Update an existing settings entry
     *
     * @param RoomSetup 	$room_setup
     *
     * @return void
     */
    public function update(RoomSetup $room_setup);

    /**
     * Get settings for object
     *
     * @param int 	$obj_id
     *
     * @throws \LogicException if no settings for obj are available
     *
     * @return RoomSetup[]
     */
    public function selectFor(int $obj_id);

    /**
     * Delete settings for obj
     *
     * @param int 	$obj_id
     *
     * @return void
     */
    public function deleteFor(int $obj_id);
}
