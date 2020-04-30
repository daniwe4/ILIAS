<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

require_once "./Services/Cron/classes/class.ilCronHookPlugin.php";


use CaT\Plugins\WorkflowReminder;

class ilWorkflowReminderPlugin extends ilCronHookPlugin
{
    use WorkflowReminder\DI;

    /**
     * @var \ILIAS\DI\Container
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
        return "WorkflowReminder";
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
}
