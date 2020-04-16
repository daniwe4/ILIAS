<?php

declare(strict_types=1);

namespace CaT\Plugins\Venues\Venues;

/**
 * Interface of help functions for venue edit gui
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface ConfigFormHelper
{
    public function addFormItems(\ilPropertyFormGUI $form);
    public function createObject(int $venue_id, array $post);

    /**
     * @return Object
     */
    public function getObject(int $venue_id, array $post);

    /**
     * @param mixed[] 	&$values
     */
    public function addValues(array &$values, Venue $venue);
}
