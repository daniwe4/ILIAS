<?php declare(strict_types=1);

namespace CaT\Plugins\ParticipationsImport\DataSources;

class Course
{
    protected $title;
    protected $crs_id;
    protected $crs_type;
    protected $begin_date;
    protected $end_date;
    protected $idd;
    protected $provider;
    protected $venue;

    const NONE_INT = -1;

    public function __construct(
        string $title,
        string $crs_id,
        string $crs_type,
        \DateTime $begin_date = null,
        \DateTime $end_date = null,
        int $idd,
        string $provider,
        string $venue
    ) {
        $this->title = $title;
        $this->crs_id = $crs_id;
        $this->crs_type = $crs_type;
        $this->begin_date = $begin_date;
        $this->end_date = $end_date;
        $this->idd = $idd;
        $this->provider = $provider;
        $this->venue = $venue;
    }

    public function title() : string
    {
        return $this->title;
    }
    public function crsId() : string
    {
        return $this->crs_id;
    }
    public function crsType() : string
    {
        return $this->crs_type;
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
    public function provider() : string
    {
        return $this->provider;
    }
    public function venue() : string
    {
        return $this->venue;
    }
}
