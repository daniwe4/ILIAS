<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use PHPUnit\Framework\TestSuite;

class ilCronHookScheduledEventsSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new self();

        // add each test class of the component
        require_once "Customizing/global/plugins/Services/Cron/CronHook/ScheduledEvents/tests/ilActionsTest.php";
        require_once "Customizing/global/plugins/Services/Cron/CronHook/ScheduledEvents/tests/ScheduledEventsJobTest.php";
        require_once "Customizing/global/plugins/Services/Cron/CronHook/ScheduledEvents/tests/ScheduleEventPluginTest.php";
        $suite->addTestSuite(ilActionsTest::class);
        $suite->addTestSuite(ScheduledEventsJobTest::class);
        $suite->addTestSuite(ScheduleEventPluginTest::class);

        return $suite;
    }
}
