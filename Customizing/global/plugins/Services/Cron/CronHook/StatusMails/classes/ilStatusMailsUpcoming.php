<?php

declare(strict_types=1);

namespace CaT\Plugins\StatusMails;

use \ILIAS\TMS\Mailing\TMSMailClerk;

/**
 * Implementation of the cron job
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilStatusMailsUpcoming extends \ilCronJob
{
    use CommonJobFunctions;

    const ID = 'statusmail_upcoming_trainings';
    const SCHEDULE_TYPE = \ilCronJob::SCHEDULE_TYPE_IN_DAYS;
    const DEFAULT_SCHEDULE_VALUE = 7;

    /**
     * @var \ilTree
     */
    protected $tree;

    public function __construct(
        Orgu\DB $orgu,
        History\DB $history,
        Mailing\MailFactory $factory,
        TMSMailClerk $clerk,
        \ilTree $tree,
        \Closure $txt
    ) {
        $this->orgu = $orgu;
        $this->history = $history;
        $this->factory = $factory;
        $this->clerk = $clerk;
        $this->tree = $tree;
        $this->txt = $txt;

        $this->cached_refs = array();
    }

    /**
     * Get relevant activities of users.
     * @param int[] $usr_ids
     * @return    History\UserActivity[]
     */
    protected function getActivityData(array $usr_ids) : array
    {
        list($start, $end) = $this->getDataTimeRange();
        return $this->history->getBookedByCourseTime($start, $end, $usr_ids);
    }

    /**
     * Get relevant date-range for this job
     * @return    \DateTime[]
     */
    protected function getDataTimeRange() : array
    {
        $now = new \DateTime();
        $offset = $this->calculateOffsetBySettings();

        $start = $now;

        $end = clone $now;
        $end->add($offset);

        return [$start, $end];
    }

    protected function getTree()
    {
        return $this->tree;
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return $this->txt('stat_upcomming_trainings_title');
    }

    public function getDescription()
    {
        return $this->txt('stat_upcomming_trainings_description');
    }
}
