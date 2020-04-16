<?php declare(strict_types=1);

use CaT\Plugins\ParticipationsImport\Mappings\ConfigStorage;
use CaT\Plugins\ParticipationsImport\Mappings\Mapping;
use CaT\Plugins\ParticipationsImport\Mappings\ParticipationStatusRelationMapping;

class ilPIParticipationMappingsConfiguratorGUI extends ilPIStatusMappingConfiguratorGUI
{
    protected function storeMapping(Mapping $mapping)
    {
        $this->cs->storeParticipationStatusMapping($mapping);
    }


    protected function getEmptyMapping() : Mapping
    {
        return new ParticipationStatusRelationMapping();
    }

    protected function loadMapping() : Mapping
    {
        return $this->cs->loadParticipationStatusMapping();
    }


    protected function getTitle() : string
    {
        return $this->plugin->txt('participation_status_mapping');
    }
}
