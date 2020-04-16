<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainerOperations\Aggregations\IliasCalendar;

/**
 * Calendar Entry
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class Entry
{
    public function __construct(
        int $cal_id,
        \DateTime $start,
        \DateTime $end,
        string $last_update,
        string $title,
        string $subtitle,
        string $description,
        string $location,
        string $informations,
        bool $fullday,
        int $auto_generated,
        int $context_id,
        int $translation_type,
        int $completion,
        bool $is_milestone,
        bool $notification
    ) {
        $this->cal_id = $cal_id;
        $this->start = $start;
        $this->end = $end;
        $this->last_update = $last_update;
        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->description = $description;
        $this->location = $location;
        $this->informations = $informations;
        $this->fullday = $fullday;
        $this->auto_generated = $auto_generated;
        $this->context_id = $context_id;
        $this->translation_type = $translation_type;
        $this->completion = $completion;
        $this->is_milestone = $is_milestone;
        $this->notification = $notification;
    }

    public function getId() : int
    {
        return $this->cal_id;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    public function getFullday() : bool
    {
        return $this->fullday;
    }

    public function getStart() : \DateTime
    {
        return $this->start;
    }

    public function getEnd() : \DateTime
    {
        return $this->end;
    }

    public function getLocation() : string
    {
        return $this->location;
    }

    public function getSubtitle() : string
    {
        return $this->subtitle;
    }

    public function getInformations() : string
    {
        return $this->informations;
    }
}
