<?php

namespace CaT\Plugins\Webinar\Participant;

/**
 * Interface only for booked participants
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface DB
{
    /**
     * Book user on webinar object
     *
     * @param int 	$obj_id
     * @param int 	$user_id
     * @param string 	$user_name
     */
    public function book($obj_id, $user_id, $user_name, $minutes = null);

    /**
     * Cancel user from webinar
     *
     * @param int 	$obj_id
     * @param int 	$user_id
     */
    public function cancel($obj_id, $user_id);
}
