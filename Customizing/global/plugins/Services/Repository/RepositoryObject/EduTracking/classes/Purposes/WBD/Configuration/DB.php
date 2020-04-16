<?php

namespace CaT\Plugins\EduTracking\Purposes\WBD\Configuration;

/**
 * Interface to store configuration infos for WBD purpose
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface DB
{
    /**
     * Get the configuration values with highest id
     *
     * @return ConfigWBD
     */
    public function select();

    /**
     * Creates a new configuration entry
     *
     * @param bool 	$available
     * @param string 	$contact
     * @param int 	$user_id
     * @param int 	$changed_by
     *
     * @return void
     */
    public function insert($available, $contact, $user_id, $changed_by);
}
