<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

require_once "./Services/Cron/classes/class.ilCronHookPlugin.php";
require_once __DIR__ . "/../vendor/autoload.php";

use CaT\Plugins\AutomaticCancelWaitinglist;

class ilAutomaticCancelWaitinglistPlugin extends ilCronHookPlugin
{
    use AutomaticCancelWaitinglist\DI;

    /**
     * Get the name of the Plugin
     *
     * @return string
     */
    public function getPluginName()
    {
        return "AutomaticCancelWaitinglist";
    }

    public function getCronJobInstances()
    {
        return [
            $this->getJobInstance()
        ];
    }

    public function getCronJobInstance($a_job_id)
    {
        return $this->getJobInstance();
    }

    protected function getJobInstance() : AutomaticCancelWaitinglist\AutomaticCancelWaitinglistJob
    {
        global $DIC;
        return $this->getPluginDI($this, $DIC)["jobs.acwaiting"];
    }
}
