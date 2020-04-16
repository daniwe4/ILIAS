<?php

declare(strict_types=1);

if (!class_exists(ilCronJob::class)) {
    require_once "Services/Cron/classes/class.ilCronJob.php";
}

/**
* Implementation of the cron job
*/
class ilSyncExtCalendarsJob extends ilCronJob
{
    const ID = "tep_sync_external_calendars";

    /**
     * @var \ilDBInterface
     */
    protected $db;
    /**
     * @var \ilComponentLogger
     */
    protected $logger;

    public function __construct(\ilDBInterface $db, \ilComponentLogger $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    public function getId() : string
    {
        return self::ID;
    }

    public function getTitle() : string
    {
        return 'Synchronize External Calendars';
    }

    public function getDescription() : string
    {
        return 'Import external calendars (TEP)';
    }

    public function hasAutoActivation()
    {
        return true;
    }

    public function hasFlexibleSchedule()
    {
        return true;
    }

    public function getDefaultScheduleType() : int
    {
        return \ilCronJob::SCHEDULE_TYPE_IN_MINUTES;
    }

    /**
     * Get schedule value
     *
     * @return int|array
     */
    public function getDefaultScheduleValue()
    {
        return 30;
    }

    protected function ping()
    {
        \ilCronManager::ping($this->getId());
    }

    /**
     * Gets called if the cronjob is started
     * Executing the ToDo's of the cronjob
     */
    public function run()
    {
        foreach ($this->getRemoteCategoryIds() as $cat_id) {
            try {
                $this->sync($cat_id);
            } catch (\Exception $e) {
                $this->logger->write($e->getMessage());
            }
            $this->ping();
        }

        $cron_result = new \ilCronJobResult();
        $cron_result->setStatus(\ilCronJobResult::STATUS_OK);
        return $cron_result;
    }

    protected function getRemoteCategoryIds() : array //int[]
    {
        $types = [
            \ilCalendarCategory::TYPE_GLOBAL,
            \ilCalendarCategory::TYPE_USR
        ];
        $loc_type = \ilCalendarCategory::LTYPE_REMOTE;

        $query = "SELECT cat_id FROM cal_categories WHERE" . PHP_EOL
            . "loc_type = " . $loc_type . PHP_EOL
            . "AND " . $this->db->in('type', $types, false, 'integer');

        $ret = [];
        $res = $this->db->query($query);
        while ($row = $res->fetchObject()) {
            $ret[] = (int) $row->cat_id;
        }

        return $ret;
    }

    protected function sync(int $category_id)
    {
        $cat = \ilCalendarCategory::getInstanceByCategoryId($category_id);
        $remote = new \ilCalendarRemoteReader($cat->getRemoteUrl());
        $remote->setUser($cat->getRemoteUser());
        $remote->setPass($cat->getRemotePass());
        $remote->read();
        $remote->import($cat);
    }
}
