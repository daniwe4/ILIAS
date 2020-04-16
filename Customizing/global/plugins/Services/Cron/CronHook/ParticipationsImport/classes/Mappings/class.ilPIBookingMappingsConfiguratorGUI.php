<?php declare(strict_types=1);

use CaT\Plugins\ParticipationsImport\Mappings\ConfigStorage;
use CaT\Plugins\ParticipationsImport\Mappings\Mapping;
use CaT\Plugins\ParticipationsImport\Mappings\BookingStatusRelationMapping;

class ilPIBookingMappingsConfiguratorGUI extends ilPIStatusMappingConfiguratorGUI
{
    protected function storeMapping(Mapping $mapping)
    {
        $this->cs->storeBookingStatusMapping($mapping);
    }


    protected function getEmptyMapping() : Mapping
    {
        return new BookingStatusRelationMapping();
    }

    protected function loadMapping() : Mapping
    {
        return $this->cs->loadBookingStatusMapping();
    }


    protected function getTitle() : string
    {
        return $this->plugin->txt('booking_status_mapping');
    }
}
