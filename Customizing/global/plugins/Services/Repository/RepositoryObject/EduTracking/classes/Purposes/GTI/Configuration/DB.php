<?php

namespace CaT\Plugins\EduTracking\Purposes\GTI\Configuration;

/**
 * Interface to store configuration infos for GTI purpose
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

    public function getTitleById(int $category_id) : string;
}
