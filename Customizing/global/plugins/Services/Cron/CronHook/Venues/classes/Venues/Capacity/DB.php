<?php

declare(strict_types=1);

namespace CaT\Plugins\Venues\Venues\Capacity;

/**
 * Inteface for contact capacity DB
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface DB
{
    public function create(
        int $id,
        int $number_rooms_overnight = null,
        int $min_person_any_room = null,
        int $max_person_any_room = null,
        int $min_room_size = null,
        int $max_room_size = null,
        int $room_count = null
    ) : Capacity;

    public function update(Capacity $capacity);
    public function delete(int $id);
}
