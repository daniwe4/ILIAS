<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use PHPUnit\Framework\TestSuite;

class ilCronHookCourseCreationSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new self();

        // add each test class of the component
        require_once "Customizing/global/plugins/Services/Cron/CronHook/CourseCreation/tests/ilCourseCreationJobTest.php";
        $suite->addTestSuite(ilCourseCreationJobTest::class);

        return $suite;
    }
}
