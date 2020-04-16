<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainerOperations\Calendar;

/**
 * This is an Entry for global Calendars.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class GlobalEntry extends Entry
{
    /**
     * @var int
     */
    protected $calendar_id;

    public function __construct(
        int $calendar_id,
        string $title,
        string $description,
        bool $fullday,
        \DateTime $start,
        \DateTime $end
    ) {
        $this->calendar_id = $calendar_id;
        parent::__construct($title, $description, $fullday, $start, $end);
    }

    public function getType() : string
    {
        return static::TYPE_GLOBAL;
    }

    public function getCalendarId() : int
    {
        return $this->calendar_id;
    }
}
