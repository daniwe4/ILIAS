<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use PHPUnit\Framework\TestSuite;

class ilCronHookCronJobSurveillanceSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new self();

        // add each test class of the component
        require_once "Customizing/global/plugins/Services/Cron/CronHook/CronJobSurveillance/tests/ActionsTest.php";
        require_once "Customizing/global/plugins/Services/Cron/CronHook/CronJobSurveillance/tests/CronJobFactoryTest.php";
        require_once "Customizing/global/plugins/Services/Cron/CronHook/CronJobSurveillance/tests/CronJobSurveillanceTest.php";
        require_once "Customizing/global/plugins/Services/Cron/CronHook/CronJobSurveillance/tests/CronJobTest.php";
        require_once "Customizing/global/plugins/Services/Cron/CronHook/CronJobSurveillance/tests/MailerTest.php";
        require_once "Customizing/global/plugins/Services/Cron/CronHook/CronJobSurveillance/tests/MailSettingsTest.php";
        require_once "Customizing/global/plugins/Services/Cron/CronHook/CronJobSurveillance/tests/StaticDBTest.php";
        require_once "Customizing/global/plugins/Services/Cron/CronHook/CronJobSurveillance/tests/SurveillanceTest.php";
        $suite->addTestSuite(ActionsTest::class);
        $suite->addTestSuite(CronJobFactoryTest::class);
        $suite->addTestSuite(CronJobSurveillanceTest::class);
        $suite->addTestSuite(CronJobTest::class);
        $suite->addTestSuite(MailerTest::class);
        $suite->addTestSuite(MailSettingsTest::class);
        $suite->addTestSuite(StaticDBTest::class);
        $suite->addTestSuite(SurveillanceTest::class);

        return $suite;
    }
}
