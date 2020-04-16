<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

use CaT\Plugins\CronJobSurveillance\Surveillance\Surveillance;

require_once "Services/Cron/classes/class.ilCronJob.php";
require_once "Services/Cron/classes/class.ilCronJobResult.php";
require_once("class.ilCronJobSurveillancePlugin.php");

/**
* Implementation of the cron job
*/
class ilCronJobSurveillanceJob extends ilCronJob
{
    public function __construct(ilCronJobSurveillancePlugin $plugin, Surveillance $surveillance)
    {
        $this->plugin = $plugin;
        $this->surveillance = $surveillance;
    }

    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return 'ccjs';
    }

    /**
     * Get the title of the job
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Cron Job Surveillance';
    }

    /**
     * Get the description of the job
     *
     * @return string
     */
    public function getDescription()
    {
        return 'aka Sherrif - alert admins of crashed jobs';
    }

    /**
     * Is to be activated on "installation"
     *
     * @return boolean
     */
    public function hasAutoActivation()
    {
        return true;
    }

    /**
     * Can the schedule be configured?
     *
     * @return boolean
     */
    public function hasFlexibleSchedule()
    {
        return true;
    }

    /**
     * Get schedule type
     *
     * @return int
     */
    public function getDefaultScheduleType()
    {
        return \ilCronJob::SCHEDULE_TYPE_IN_HOURS;
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
     * Gets called if the cronjob is started
     * Executing the ToDo's of the cronjob
     */
    public function run()
    {
        $this->surveillance->checkJobs();
        $cron_result = new \ilCronJobResult();
        $cron_result->setStatus(\ilCronJobResult::STATUS_OK);
        return $cron_result;
    }
}
