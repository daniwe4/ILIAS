<?php

include_once("./Services/Cron/classes/class.ilCronHookPlugin.php");
require_once(__DIR__ . "/class.ilSyncExtCalendarsJob.php");

/**
 * Plugin base class. Keeps all information the plugin needs
 */
class ilSyncExtCalendarsPlugin extends ilCronHookPlugin
{
    /**
     * Get the name of the Plugin
     *
     * @return string
     */
    public function getPluginName()
    {
        return "SyncExtCalendars";
    }

    /**
     * Get an array with 1 to n numbers of cronjob objects
     *
     * @return ilCronJob[]
     */
    public function getCronJobInstances()
    {
        return [$this->getCronJobInstance(ilSyncExtCalendarsJob::ID)];
    }

    /**
     * Get a single cronjob object
     *
     * @return ilCronJob
     */
    public function getCronJobInstance($a_job_id)
    {
        if ($a_job_id != ilSyncExtCalendarsJob::ID) {
            throw new \InvalidArgumentException("Unknown id for SyncExtCalendars: '$a_job_id'");
        }

        global $DIC;
        $db = $DIC['ilDB'];
        $logger = $DIC->logger()->root();
        return new ilSyncExtCalendarsJob($db, $logger);
    }
}
