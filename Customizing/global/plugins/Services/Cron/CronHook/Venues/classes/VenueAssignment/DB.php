<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

declare(strict_types=1);

namespace CaT\Plugins\Venues\VenueAssignment;

/**
 * Describes database functionalities for course/venue relation
 *
 * @author Nils Haagen	<nils.haagen@concepts-and-training.de>
 */
interface DB
{
    public function createListVenueAssignment(int $crs_id, int $venue_id, string $venue_additional) : ListAssignment;
    public function createCustomVenueAssignment(int $crs_id, string $text) : CustomAssignment;

    /**
     * @return VenueAssignment | false
     */
    public function select(int $crs_id);
    public function update(VenueAssignment $venue_assignment);
    public function delete(int $crs_id);
}
