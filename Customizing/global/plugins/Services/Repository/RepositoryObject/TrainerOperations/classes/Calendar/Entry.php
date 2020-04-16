<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainerOperations\Calendar;

/**
 * An Entry is the actual event to be displayed in a calendar.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
abstract class Entry
{
    const TYPE_SESSION = 'session';
    const TYPE_GLOBAL = 'global';
    const TYPE_PERSONAL = 'personal';

    /**
     * @var bool
     */
    protected $fullday;
    /**
     * @var \DateTime
     */
    protected $start;
    /**
     * @var \DateTime
     */
    protected $end;
    /**
     * @var string
     */
    protected $title;
    /**
     * @var string
     */
    protected $description;


    public function __construct(
        string $title,
        string $description,
        bool $fullday,
        \DateTime $start,
        \DateTime $end
    ) {
        $this->title = $title;
        $this->description = $description;
        $this->fullday = $fullday;
        $this->start = $start;
        $this->end = $end;
    }

    abstract public function getType() : string;

    public function getTitle() : string
    {
        return $this->title;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    public function getStart() : \DateTime
    {
        return $this->start;
    }

    public function getEnd() : \DateTime
    {
        return $this->end;
    }

    public function isFullDay() : bool
    {
        return $this->fullday;
    }
}
