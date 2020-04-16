<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainerOperations\Calendar;

/**
 * A Schedule bundles Entries.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class Schedule
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var Entry[]
     */
    protected $entries;

    /**
     * @param string $id
     * @param string $title
     * @param Entry[] $entries
     */
    public function __construct(string $id, string $title, array $entries)
    {
        $this->id = $id;
        $this->title = $title;
        $this->entries = $entries;
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function addEntry(Entry $entry) : Schedule
    {
        $this->entries[] = $entry;
        return $this;
    }

    /**
     * @return Entry[]
     */
    public function getEntries() : array
    {
        return $this->entries;
    }

    /**
     * @return Entry[]
     */
    public function getEntryByDay(\DateTime $date) : array
    {
        $day_start = clone $date;
        $day_end = clone $date;
        $day_end = $day_end->modify('+1 day')->modify('-1 minute');

        //TODO: adjust timezones before checking?
        $relevant = array_filter(
            $this->getEntries(),
            function ($event) use ($day_start, $day_end) {
                return (
                    $event->getStart() >= $day_start &&
                    $event->getEnd() <= $day_end
                ) || (
                    $day_start >= $event->getStart() &&
                    $day_end <= $event->getEnd()
                );
            }
        );

        return $relevant;
    }
}
