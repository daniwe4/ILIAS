<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainerOperations\Calendar;

/**
 * This is an Entry for global Calendars.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class PersonalEntry extends Entry
{
    /**
     * @var int
     */
    protected $usr_id;

    /**
     * @var bool
     */
    protected $undisclosed;

    /**
     * @var int
     */
    protected $calendar_id;

    /**
     * @var string
     */
    protected $location;
    /**
     * @var string
     */
    protected $informations;
    /**
     * @var string
     */
    protected $subtitle;

    public function __construct(
        int $usr_id,
        bool $undisclosed,
        int $calendar_id,
        string $title,
        string $description,
        bool $fullday,
        \DateTime $start,
        \DateTime $end,
        string $location,
        string $informations,
        string $subtitle

    ) {
        $this->usr_id = $usr_id;
        $this->undisclosed = $undisclosed;
        $this->calendar_id = $calendar_id;
        $this->location = $location;
        $this->informations = $informations;
        $this->subtitle = $subtitle;
        parent::__construct($title, $description, $fullday, $start, $end);
    }

    public function getType() : string
    {
        return static::TYPE_PERSONAL;
    }

    public function getCalendarId() : int
    {
        return $this->calendar_id;
    }

    public function getUserId() : int
    {
        return $this->usr_id;
    }

    public function getPrivate() : bool
    {
        return $this->undisclosed;
    }

    public function getLocation() : string
    {
        return $this->location;
    }

    public function getInformations() : string
    {
        return $this->informations;
    }

    public function getSubtitle() : string
    {
        return $this->subtitle;
    }
}
