<?php declare(strict_types=1);

namespace CaT\Plugins\ParticipationsImport\DataTargets;

class Course
{
    protected $crs_id;
    protected $crs_title;
    protected $crs_type;
    protected $begind_date;
    protected $end_date;
    protected $idd;
    protected $provider;
    protected $venue;

    const NONE_INT = -1;

    public function __construct(
        int $crs_id,
        string $crs_title,
        string $crs_type,
        \DateTime $begind_date = null,
        \DateTime $end_date = null,
        int $idd,
        string $provider,
        string $venue
    ) {
        $this->crs_id = $crs_id;
        $this->crs_title = $crs_title;
        $this->crs_type = $crs_type;
        $this->begind_date = $begind_date;
        $this->end_date = $end_date;
        $this->idd = $idd;
        $this->provider = $provider;
        $this->venue = $venue;
    }

    public function crsId() : int
    {
        return $this->crs_id;
    }
    public function crsTitle() : string
    {
        return $this->crs_title;
    }
    public function crsType() : string
    {
        return $this->crs_type;
    }
    public function beginDate()
    {
        return $this->begind_date;
    }
    public function endDate()
    {
        return $this->end_date;
    }
    public function idd() : int
    {
        return $this->idd;
    }
    public function provider() : string
    {
        return $this->provider;
    }
    public function venue() : string
    {
        return $this->venue;
    }
}
