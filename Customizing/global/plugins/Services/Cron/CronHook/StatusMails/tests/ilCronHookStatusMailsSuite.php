<?php

/* Copyright (c) 2020 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

use PHPUnit\Framework\TestSuite;
use CaT\Plugins\StatusMails\Course;
use CaT\Plugins\StatusMails\History;

class ilCronHookStatusMailsSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new self();

        $suite->addTestSuite(Course\CourseFlagsTest::class);
        $suite->addTestSuite(History\UserActivityTest::class);

        return $suite;
    }
}
