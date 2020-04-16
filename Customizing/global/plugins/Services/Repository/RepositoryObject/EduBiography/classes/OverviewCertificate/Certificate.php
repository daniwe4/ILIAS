<?php

declare(strict_types=1);

namespace CaT\Plugins\EduBiography\OverviewCertificate;

class Certificate
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
     * @var int
     */
    protected $received_idd_minutes;

    /**
     * @var bool
     */
    protected $part_document;

    /**
     * @var bool
     */
    protected $show_overview_download;

    public function __construct(
        int $id,
        string $title,
        \DateTime $start,
        \DateTime $end,
        int $min_idd_value,
        int $received_idd_minutes,
        bool $part_document,
        bool $show_overview_download
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->start = $start;
        $this->end = $end;
        $this->min_idd_value = $min_idd_value;
        $this->received_idd_minutes = $received_idd_minutes;
        $this->part_document = $part_document;
        $this->show_overview_download = $show_overview_download;
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

    public function getReceivedIddMinutes() : int
    {
        return $this->received_idd_minutes;
    }

    public function isPartDocument() : bool
    {
        return $this->part_document;
    }

    public function isShowOverviewDownload() : bool
    {
        return $this->show_overview_download;
    }
}
