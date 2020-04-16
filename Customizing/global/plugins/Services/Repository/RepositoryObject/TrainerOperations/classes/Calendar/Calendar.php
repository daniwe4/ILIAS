<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainerOperations\Calendar;

/**
 * A calendar has one ore more schedules and a range.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class Calendar
{
    /**
     * @var \DatePeriod
     */
    protected $range;
    /**
     * @var Schedule[]
     */
    protected $schedules;

    public function __construct(\DatePeriod $range, array $columns)
    {
        $this->range = $range;
        $this->columns = $columns;
    }

    public function getRange() : \DatePeriod
    {
        return $this->range;
    }

    /**
     * @param Schedule[] $schedules
     */
    public function withSchedules(array $schedules) : Calendar
    {
        $clone = clone $this;
        $clone->schedules = $schedules;
        return $clone;
    }

    /**
     * @return Schedule[]
     */
    public function getSchedules() : array
    {
        return $this->schedules;
    }
}
