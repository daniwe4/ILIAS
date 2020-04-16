<?php
namespace CaT\Plugins\Accomodation\Venue;

/**
 * Interface for DB handle of venues
 *
 * @author 	Nils Haagen 	<nils.haagen@concepts-and-training.de>
 */
interface DB
{

    /**
     * Get available venues from the venue-plugin
     * with id=>title
     * @return  array<int,string>
     */
    public function getVenueListFromPlugin();
}
