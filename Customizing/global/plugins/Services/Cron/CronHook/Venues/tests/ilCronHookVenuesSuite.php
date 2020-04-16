<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use PHPUnit\Framework\TestSuite;

class ilCronHookVenuesSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new self();

        // add each test class of the component
        require_once "Customizing/global/plugins/Services/Cron/CronHook/Venues/tests/AddressTest.php";
        require_once "Customizing/global/plugins/Services/Cron/CronHook/Venues/tests/CapacityTest.php";
        require_once "Customizing/global/plugins/Services/Cron/CronHook/Venues/tests/ConditionsTest.php";
        require_once "Customizing/global/plugins/Services/Cron/CronHook/Venues/tests/ContactTest.php";
        require_once "Customizing/global/plugins/Services/Cron/CronHook/Venues/tests/CostsTest.php";
        require_once "Customizing/global/plugins/Services/Cron/CronHook/Venues/tests/GeneralTest.php";
        require_once "Customizing/global/plugins/Services/Cron/CronHook/Venues/tests/RatingTest.php";
        require_once "Customizing/global/plugins/Services/Cron/CronHook/Venues/tests/ServiceTest.php";
        require_once "Customizing/global/plugins/Services/Cron/CronHook/Venues/tests/TagsTest.php";
        require_once "Customizing/global/plugins/Services/Cron/CronHook/Venues/tests/VenueAssignTest.php";
        require_once "Customizing/global/plugins/Services/Cron/CronHook/Venues/tests/VenueTest.php";
        $suite->addTestSuite(AddressTest::class);
        $suite->addTestSuite(CapacityTest::class);
        $suite->addTestSuite(ConditionsTest::class);
        $suite->addTestSuite(ContactTest::class);
        $suite->addTestSuite(CostsTest::class);
        $suite->addTestSuite(GeneralTest::class);
        $suite->addTestSuite(RatingTest::class);
        $suite->addTestSuite(ServiceTest::class);
        $suite->addTestSuite(TagsTest::class);
        $suite->addTestSuite(VenueAssignTest::class);
        $suite->addTestSuite(VenueTest::class);

        return $suite;
    }
}
