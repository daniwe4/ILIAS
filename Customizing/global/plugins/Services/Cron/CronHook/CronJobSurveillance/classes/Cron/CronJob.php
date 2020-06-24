<?php
namespace CaT\Plugins\CronJobSurveillance\Cron;

/**
 * CronJobs provide information about their status, interval and when they were
 * last executed.
 *
 * @author Nils Haagen 	<nils.haagen@concepts-and-training.de>
 */
class CronJob
{
    private $id;
    private $interval;
    private $finished;
    private $last_start;

    public function __construct(string $id, \DateInterval $interval, bool $finished, ?\DateTime $last_start)
    {
        $this->id = $id;
        $this->interval = $interval;
        $this->finished = $finished;
        $this->last_start = $last_start;
    }

    /**
     * @return 	string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return 	\DateInterval
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * @return 	bool
     */
    public function getIsFinished()
    {
        return $this->finished;
    }

    /**
     * @return 	\DateTime | null
     */
    public function getLastRunStart()
    {
        if (is_null($this->last_start)) {
            return null;
        }
        return clone $this->last_start;
    }
}
