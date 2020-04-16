<?php

declare(strict_types=1);

namespace CaT\Plugins\EduBiography\ParticipationDocument;

use CaT\Plugins\EduBiography\Config\OverviewCertificate\Schedules\Schedule;

class Participation
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var \DateTime
     */
    protected $begin_date;

    /**
     * @var \DateTime
     */
    protected $end_date;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var string
     */
    protected $provider;

    /**
     * @var int
     */
    protected $idd_minutes;

    public function __construct(
        string $title,
        string $type,
        \DateTime $begin_date,
        \DateTime $end_date,
        string $content,
        string $provider,
        int $idd_minutes
    ) {
        $this->title = $title;
        $this->type = $type;
        $this->begin_date = $begin_date;
        $this->end_date = $end_date;
        $this->content = $content;
        $this->provider = $provider;
        $this->idd_minutes = $idd_minutes;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function getBeginDate() : \DateTime
    {
        return $this->begin_date;
    }

    public function getEndDate() : \DateTime
    {
        return $this->end_date;
    }

    public function getContent() : string
    {
        return $this->content;
    }

    public function getProvider() : string
    {
        return $this->provider;
    }

    public function getIddMinutes() : int
    {
        return $this->idd_minutes;
    }
}
