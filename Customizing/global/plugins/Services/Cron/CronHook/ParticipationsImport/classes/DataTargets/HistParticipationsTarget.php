<?php declare(strict_types=1);

namespace CaT\Plugins\ParticipationsImport\DataTargets;

use Psr\Log\LoggerInterface as LoggerInterface;

class HistParticipationsTarget extends HistTarget implements ParticipationsTarget
{
    const TABLE_HHD_USRCRS = 'hhd_usrcrs';
    const TABLE_HST_USRCRS = 'hst_usrcrs';

    const FIELD_CRS_ID = 'crs_id';
    const FIELD_USR_ID = 'usr_id';
    const FIELD_BOOKING_STATUS = 'booking_status';
    const FIELD_PARTICIPATION_STATUS = 'participation_status';
    const FIELD_BOOKING_DATE = 'booking_date';
    const FIELD_PS_ACQUIRED_DATE = 'ps_acquired_date';
    const FIELD_IDD = 'idd_learning_time';


    public function importParticipation(Participation $participation)
    {
        $this->updateData(
            $this->extractData($participation)
        );
    }

    public function extractData(Participation $participation) : array
    {
        $data = [];
        $data[self::FIELD_CRS_ID] = ['integer',$participation->crsId()];
        $data[self::FIELD_USR_ID] = ['integer',$participation->usrId()];
        $data[self::FIELD_BOOKING_STATUS] = ['text',$participation->bookingStatus()];
        $data[self::FIELD_PARTICIPATION_STATUS] = ['text',$participation->participationStatus()];
        $data[self::FIELD_BOOKING_DATE] = ['text',$participation->beginDate() ? $participation->beginDate()->format('Y-m-d') : null];
        $data[self::FIELD_PS_ACQUIRED_DATE] = ['text',$participation->endDate() ? $participation->endDate()->format('Y-m-d') : null];
        $data[self::FIELD_IDD] = ['integer',$participation->idd() >= 0 ? $participation->idd() : null];
        return $data;
    }

    public function getPkFromData(array $data) : array
    {
        return [
            self::FIELD_CRS_ID => ['integer',$data[self::FIELD_CRS_ID][1]],
            self::FIELD_USR_ID => ['integer',$data[self::FIELD_USR_ID][1]]
        ];
    }
    protected function histTable() : string
    {
        return self::TABLE_HST_USRCRS;
    }
    protected function headTable() : string
    {
        return self::TABLE_HHD_USRCRS;
    }
}
