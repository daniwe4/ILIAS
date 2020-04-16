<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

require_once "./Services/Cron/classes/class.ilCronHookPlugin.php";
require_once __DIR__ . "/../vendor/autoload.php";

use CaT\Plugins\WBDCommunicator\DI;

class ilWBDCommunicatorPlugin extends ilCronHookPlugin
{
    use DI;

    /**
     * @var Pimple\Container
     */
    protected $dic;

    public function __construct()
    {
        parent::__construct();

        global $DIC;
        $this->dic = $DIC;
    }

    /**
     * @inheritdoc
     */
    public function getPluginName()
    {
        return "WBDCommunicator";
    }

    /**
     * @inheritdoc
     */
    public function getCronJobInstances()
    {
        return $this->getPluginDIC($this, $this->dic)["jobs.getall"];
    }

    /**
     * @inheritdoc
     */
    public function getCronJobInstance($a_job_id)
    {
        $fnc = $this->getPluginDIC($this, $this->dic)["jobs.get"];
        return call_user_func($fnc, $a_job_id);
    }

    public function getAnnouncementStartDate()
    {
        try {
            /** @var CaT\Plugins\WBDCommunicator\Config\OperationLimits\DB $db */
            $db = $this->getPluginDIC($this, $this->dic)["config.oplimits.db"];
            $start_date = $db->getStartDateForAnnouncement();
        } catch (LogicException $e) {
            $start_date = null;
        }

        return $start_date;
    }
}
