<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

declare(strict_types=1);

namespace CaT\Plugins\Venues\Venues;

/**
 * Describes database functionalities for venues
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
interface DB
{
    /**
     * @throws \Exception 	if no venue was found
     */
    public function getVenue(int $id) : Venue;

    /**
     * @param int[] | []
     *
     * @return Venue[]
     */
    public function getAllVenues(string $order_column, string $order_direction, array $filtered_tags) : array;
}
