<?php

namespace CaT\Plugins\Webinar\Participant;

/**
 * Interface only for booked participants
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface DB
{
    public function book(int $obj_id, int $user_id, string $user_name, ?int $minutes = null) : void;
    public function cancel(int $obj_id, int $user_id) : void;
}
