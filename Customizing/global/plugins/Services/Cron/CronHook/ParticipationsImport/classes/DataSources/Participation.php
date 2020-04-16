<?php declare(strict_types=1);

namespace CaT\Plugins\ParticipationsImport\DataSources;

class Participation
{
    protected $extern_crs_id;
    protected $extern_usr_id;
    protected $booking_status;
    protected $participation_status;
    protected $begin_date;
    protected $end_date;
    protected $idd;

    const NONE_INT = -1;

    public function __construct(
        string $extern_crs_id,
        string $extern_usr_id,
        string $booking_status,
        string $participation_status,
        \DateTime $begin_date = null,
        \DateTime $end_date = null,
        int $idd
    ) {
        $this->extern_crs_id = $extern_crs_id;
        $this->extern_usr_id = $extern_usr_id;
        $this->booking_status = $booking_status;
        $this->participation_status = $participation_status;
        $this->begin_date = $begin_date;
        $this->end_date = $end_date;
        $this->idd = $idd;
    }


    public function externCrsId() : string
    {
        return $this->extern_crs_id;
    }
    public function externUsrId() : string
    {
        return $this->extern_usr_id;
    }
    public function bookingStatus() : string
    {
        return $this->booking_status;
    }
    public function participationStatus() : string
    {
        return $this->participation_status;
    }
    public function beginDate()
    {
        return $this->begin_date;
    }
    public function endDate()
    {
        return $this->end_date;
    }
    public function idd() : int
    {
        return $this->idd;
    }
}
