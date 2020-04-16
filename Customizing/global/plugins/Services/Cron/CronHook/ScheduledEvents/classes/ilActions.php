<?php
namespace CaT\Plugins\ScheduledEvents;

/**
 * Communication class between front- and backend.
 * E.g. GUI only use this class to get information from ILIAS DB.
 */
class ilActions
{

    /**
     * @var \ILIAS\TMS\ScheduledEvents\DB
     */
    protected $schedule;

    public function __construct(\ILIAS\TMS\ScheduledEvents\DB $schedule)
    {
        $this->schedule = $schedule;
    }

    /**
     * @return	\ILIAS\TMS\ScheduledEvents\Events[]
     */
    public function getAllDueEvents()
    {
        return $this->schedule->getAllDue();
    }

    /**
     * @return	\ILIAS\TMS\ScheduledEvents\Events[]
     */
    public function getAllEvents()
    {
        return $this->schedule->getAll();
    }
}
