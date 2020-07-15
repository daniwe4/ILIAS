<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/Cron/classes/class.ilCronJob.php";

/**
* Implementation of cron job for venues
*
* @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
*/
class ilVenuesJob extends ilCronJob
{
    public function __construct()
    {
        $this->plugin =
            ilPlugin::getPluginObject(
                IL_COMP_SERVICE,
                "Cron",
                "crnhk",
                ilPlugin::lookupNameForId(IL_COMP_SERVICE, "Cron", "crnhk", $this->getId())
            );
    }

    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return "venues";
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->plugin->txt("title");
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->plugin->txt("description");
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
        return ilCronJob::SCHEDULE_TYPE_DAILY;
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
     * Get called if the cronjob is started
     * Executing the ToDo's of the cronjob
     */
    public function run()
    {
    }
}
