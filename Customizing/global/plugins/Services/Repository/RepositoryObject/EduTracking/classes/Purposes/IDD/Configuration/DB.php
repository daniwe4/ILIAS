<?php

namespace CaT\Plugins\EduTracking\Purposes\IDD\Configuration;

/**
 * Interface to store configuration infos for IDD purpose
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface DB
{
    /**
     * Get the configuration values with highest id
     *
     * @return ConfigIDD
     */
    public function select();

    /**
     * Creates a new configuration entry
     *
     * @param bool 	$available
     * @param int 	$changed_by
     *
     * @return void
     */
    public function insert($available, $changed_by);
}
