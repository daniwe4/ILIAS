<?php

declare(strict_types=1);

namespace CaT\Plugins\EduBiography\Config\OverviewCertificate\Schedules;

class Schedule
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var \DateTime
     */
    protected $start;

    /**
     * @var \DateTime
     */
    protected $end;

    /**
     * @var int
     */
    protected $min_idd_value;

    /**
     * @var bool
     */
    protected $active;

    /**
     * @var bool
     */
    protected $participations_document_active;

    public function __construct(
        int $id,
        string $title,
        \DateTime $start,
        \DateTime $end,
        int $min_idd_value,
        bool $active,
        bool $participations_document_active
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->start = $start;
        $this->end = $end;
        $this->min_idd_value = $min_idd_value;
        $this->active = $active;
        $this->participations_document_active = $participations_document_active;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function getStart() : \DateTime
    {
        return $this->start;
    }

    public function getEnd() : \DateTime
    {
        return $this->end;
    }

    public function getMinIddValue() : int
    {
        return $this->min_idd_value;
    }

    public function isActive() : bool
    {
        return $this->active;
    }

    public function isParticipationsDocumentActive() : bool
    {
        return $this->participations_document_active;
    }

    public function withTitle(string $title) : Schedule
    {
        $clone = clone $this;
        $clone->title = $title;
        return $clone;
    }

    public function withStart(\DateTime $start) : Schedule
    {
        $clone = clone $this;
        $clone->start = $start;
        return $clone;
    }

    public function withEnd(\DateTime $end) : Schedule
    {
        $clone = clone $this;
        $clone->end = $end;
        return $clone;
    }

    public function withMinIddValue(int $min_idd_value) : Schedule
    {
        $clone = clone $this;
        $clone->min_idd_value = $min_idd_value;
        return $clone;
    }

    public function withParticipationsDocumentActive(bool $participations_document_active) : Schedule
    {
        $clone = clone $this;
        $clone->participations_document_active = $participations_document_active;
        return $clone;
    }
}
