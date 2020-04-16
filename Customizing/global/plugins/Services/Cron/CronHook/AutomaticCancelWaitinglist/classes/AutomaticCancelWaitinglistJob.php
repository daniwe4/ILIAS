<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\AutomaticCancelWaitinglist;

if (!class_exists(ilCronJob::class)) {
    require_once "Services/Cron/classes/class.ilCronJob.php";
}

class AutomaticCancelWaitinglistJob extends \ilCronJob
{
    const ID = "acwaiting";

    /**
     * @var Database\DB
     */
    protected $db_crs;

    /**
     * @var Log\DB
     */
    protected $db_log;

    public function __construct(Database\DB $db_crs, Log\DB $db_log)
    {
        $this->db_crs = $db_crs;
        $this->db_log = $db_log;
    }

    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return static::ID;
    }

    public function getTitle()
    {
        return "Sagt offene Wartelisten ab.";
    }

    public function getDescription()
    {
        return "Wenn der Zeitpukt gekommen ist, dass die Warteliste abgesagt werden, wird das erledigt.";
    }

    /**
     * Is to be activated on "installation"
     *
     * @return boolean
     */
    public function hasAutoActivation()
    {
        return false;
    }

    /**
     * Can the schedule be configured?
     *
     * @return boolean
     */
    public function hasFlexibleSchedule()
    {
        return false;
    }

    /**
     * Get schedule type
     *
     * @return int
     */
    public function getDefaultScheduleType()
    {
        return \ilCronJob::SCHEDULE_TYPE_DAILY;
    }

    /**
     * Get schedule value
     *
     * @return int|array
     */
    public function getDefaultScheduleValue()
    {
        return 1;
    }

    /**
     * Processes the creation requests one after the other.
     *
     * @throws \Exception
     * @return \ilCronJobResult
     */
    public function run()
    {
        $cron_result = new \ilCronJobResult();
        $crs_datas = $this->db_crs->getCancableCourses();

        foreach ($crs_datas as $crs_ref_id => $crs_data) {
            $today = date("Y-m-d");
            $begin_date = $crs_data->getBeginDate();

            $mod_infos = $crs_data->getModalitiesInfos();
            $begin_date->sub(new \DateInterval("P" . $mod_infos["cancellation"] . "D"));

            if ($today >= $begin_date->format("Y-m-d")) {
                try {
                    $this->cancelList($crs_ref_id, $mod_infos["xbkm_ref_id"]);
                    $this->db_log->logSuccess($crs_ref_id, $today);
                } catch (\Exception $e) {
                    $this->db_log->logFail($crs_ref_id, $today, $e->getMessage());
                }
            }
        }

        $cron_result->setStatus(\ilCronJobResult::STATUS_OK);
        return $cron_result;
    }

    protected function cancelList(int $crs_ref_id, int $xbkm_ref_id)
    {
        $xbkm = \ilObjectFactory::getInstanceByRefId($xbkm_ref_id);
        $xbkm->cancelWaitinglistFor($crs_ref_id);
    }
}
