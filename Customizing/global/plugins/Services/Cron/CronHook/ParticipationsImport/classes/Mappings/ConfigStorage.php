<?php declare(strict_types=1);

namespace CaT\Plugins\ParticipationsImport\Mappings;

interface ConfigStorage
{
    public function loadCurrentConfig() : Config;
    public function storeConfigAsCurrent(Config $cfg);
    public function loadParticipationStatusMapping() : ParticipationStatusMapping;
    public function storeParticipationStatusMapping(ParticipationStatusMapping $psm);
    public function loadBookingStatusMapping() : BookingStatusMapping;
    public function storeBookingStatusMapping(BookingStatusMapping $bsm);
}
