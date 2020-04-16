<?php declare(strict_types=1);

namespace CaT\Plugins\ParticipationsImport;

class ParticipationTranslator
{
    protected $u_m;
    protected $c_m;
    protected $ps_m;
    protected $bs_m;

    public function __construct(
        Mappings\UserMapping $u_m,
        Mappings\ConfigStorage $c_s,
        Mappings\CourseMapping $c_m
    ) {
        $this->u_m = $u_m;
        $this->ps_m = $c_s->loadParticipationStatusMapping();
        $this->bs_m = $c_s->loadBookingStatusMapping();
        $this->c_m = $c_m;
    }

    /**
     * @throws Exceptions\NoUserFoundException
     */
    public function translateParticipation(
        DataSources\Participation $src_participation
    ) : DataTargets\Participation {
        $usr_id = $this->u_m->iliasUserIdForExternUserId(
            $src_participation->externUsrId()
        );
        if ($usr_id === Mappings\Mapping::NO_MAPPING_FOUND_INT) {
            throw new NoMappingFoundException(
                'no mapping found for ' . $src_participation->externUsrId()
            );
        }

        $crs_id = $this->c_m->iliasCrsIdForExternCrsId(
            $src_participation->externCrsId(),
            false
        );
        if ($crs_id === Mappings\Mapping::NO_MAPPING_FOUND_INT) {
            throw new NoMappingFoundException(
                'no mapping found for ' . $src_participation->externCrsId()
            );
        }

        $booking_status = $this->bs_m->ilBookingStatusForExternStatus(
            $src_participation->bookingStatus()
        );
        if ($booking_status === Mappings\Mapping::NO_MAPPING_FOUND_STRING) {
            throw new NoMappingFoundException(
                'no mapping found for ' . $src_participation->bookingStatus()
            );
        }

        $participation_status = $this->ps_m->ilParticipationStatusForExternStatus(
            $src_participation->participationStatus()
        );
        if ($participation_status === Mappings\Mapping::NO_MAPPING_FOUND_STRING) {
            throw new NoMappingFoundException(
                'no mapping found for ' . $src_participation->participationStatus()
            );
        }
        return new DataTargets\Participation(
            $crs_id,
            $usr_id,
            $booking_status,
            $participation_status,
            $src_participation->beginDate(),
            $src_participation->endDate(),
            $src_participation->idd()
        );
    }
}
